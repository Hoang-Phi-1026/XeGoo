<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/login');
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hỗ trợ khách hàng - XeGoo</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/main.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/chat.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <!-- Redesigned customer support chat layout - removed left sidebar, full-width layout with modern header -->
    <div class="chat-wrapper customer-chat-wrapper">
        <div class="chat-container customer-chat-container">
            <!-- Main Chat Area - Full Width -->
            <main class="chat-main customer-chat-main">
                <!-- Chat Header with End Session Button -->
                <header class="chat-header customer-header">
                    <div class="header-content">
                        <h2 class="header-title">Hỗ trợ Khách Hàng</h2>
                        <p class="header-subtitle">Chúng tôi sẵn sàng giúp bạn 24/7</p>
                    </div>
                    <div class="header-actions">
                        <div class="status-indicator-header">
                            <span class="status-dot" id="statusDot"></span>
                            <span class="status-text" id="statusText">Chờ kết nối</span>
                        </div>
                        <button id="closeSessionBtn" class="btn-end-session" title="Kết thúc phiên chat">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Kết thúc</span>
                        </button>
                    </div>
                </header>

                <!-- Messages Container -->
                <div class="messages-container" id="chatMessages">
                    <div class="welcome-message">
                        <div class="welcome-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h3>Xin chào!</h3>
                        <p>Hãy gửi tin nhắn đầu tiên để bắt đầu cuộc trò chuyện với nhân viên hỗ trợ</p>
                    </div>
                </div>

                <!-- Input Area -->
                <footer class="chat-footer">
                    <div class="input-group">
                        <input 
                            type="text" 
                            id="messageInput" 
                            class="message-input" 
                            placeholder="Nhập tin nhắn của bạn..."
                            autocomplete="off"
                        >
                        <button id="sendBtn" class="btn-send" title="Gửi">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </footer>
            </main>
        </div>
    </div>

    <script>
        const baseUrl = '<?php echo BASE_URL; ?>';
        const currentUserId = <?php echo $_SESSION['user_id']; ?>;
        let maPhien = null;
        let lastMessageId = 0;
        let sessionCreated = false;

        document.getElementById('sendBtn').addEventListener('click', sendMessage);
        document.getElementById('messageInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
        document.getElementById('closeSessionBtn').addEventListener('click', closeSession);

        function sendMessage() {
            const noiDung = document.getElementById('messageInput').value.trim();
            if (!noiDung) return;

            document.getElementById('messageInput').value = '';

            if (!sessionCreated) {
                createSession(noiDung);
            } else {
                sendMessageToSession(noiDung);
            }
        }

        function createSession(noiDung) {
            fetch(baseUrl + '/api/chat/create-session', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    maPhien = data.session_id;
                    sessionCreated = true;
                    sendMessageToSession(noiDung);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function sendMessageToSession(noiDung) {
            fetch(baseUrl + '/api/chat/send-message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    maPhien: maPhien,
                    noiDung: noiDung
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    lastMessageId = data.maTinNhan;
                    
                    updateStatus('Chờ kết nối', 'pending');
                    
                    loadMessages();
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function loadMessages() {
            if (!maPhien) return;

            fetch(baseUrl + '/api/chat/get-messages?maPhien=' + maPhien + '&lastMessageId=' + lastMessageId)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.messages.length > 0) {
                        const messagesContainer = document.getElementById('chatMessages');
                        
                        if (messagesContainer.querySelector('.welcome-message')) {
                            messagesContainer.innerHTML = '';
                        }

                        data.messages.forEach(msg => {
                            const isCurrentUser = msg.nguoiGui == currentUserId;
                            addMessageToUI(msg, isCurrentUser);
                            lastMessageId = msg.maTinNhan;
                        });
                        scrollToBottom();
                        
                        if (data.messages.some(msg => msg.vaiTroNguoiGui == 'Nhân viên')) {
                            updateStatus('Đang chat', 'connected');
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function addMessageToUI(msg, isCurrentUser) {
            const messageClass = isCurrentUser ? 'message-sent' : 'message-received';
            const senderName = msg.tenNguoiDung || msg.tenNhanVien || 'Nhân viên hỗ trợ';
            const senderRole = msg.vaiTroNguoiGui || 'Nhân viên';
            const senderAvatar = senderName.charAt(0).toUpperCase();
            const roleClass = senderRole === 'Tài xế' ? 'driver' : senderRole === 'Nhân viên' ? 'staff' : 'customer';
            
            const messageHtml = `
                <div class="message-wrapper ${messageClass}">
                    <div class="message-avatar">
                        <div class="avatar-circle ${roleClass}">${senderAvatar}</div>
                    </div>
                    <div class="message-content">
                        <div class="message-header">
                            <span class="message-sender">${senderName}</span>
                            <span class="role-badge ${roleClass}">${senderRole}</span>
                            <span class="message-time">${new Date(msg.ngayTao).toLocaleTimeString('vi-VN', {hour: '2-digit', minute: '2-digit'})}</span>
                        </div>
                        <div class="message-bubble">
                            <div class="message-text">${msg.noiDung}</div>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('chatMessages').insertAdjacentHTML('beforeend', messageHtml);
        }

        function updateStatus(text, status) {
            document.getElementById('statusText').textContent = text;
            document.getElementById('statusDot').className = 'status-dot ' + status;
        }

        function scrollToBottom() {
            const container = document.getElementById('chatMessages');
            container.scrollTop = container.scrollHeight;
        }

        function closeSession() {
            if (confirm('Bạn có chắc chắn muốn đóng phiên chat này?')) {
                if (maPhien) {
                    fetch(baseUrl + '/api/chat/close-session', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            maPhien: maPhien
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Phiên chat đã được đóng');
                            window.location.href = baseUrl + '/';
                        }
                    })
                    .catch(error => console.error('Error:', error));
                } else {
                    window.location.href = baseUrl + '/';
                }
            }
        }

        setInterval(loadMessages, 2000);
    </script>
</body>
</html>
