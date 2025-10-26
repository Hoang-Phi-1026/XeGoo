<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/login');
    exit();
}

require_once __DIR__ . '/../../models/Chat.php';
$chatModel = new Chat();
$maNguoiDung = $_SESSION['user_id'];
$vaiTro = $_SESSION['user_role'];

// Get or create session
$session = $chatModel->createOrGetSession($maNguoiDung, $vaiTro);
$maPhien = $session ? $session['maPhien'] : null;
$sessionStatus = $session ? $session['trangThai'] : null;

// Get existing messages if session exists and is active
$existingMessages = [];
if ($maPhien && in_array($sessionStatus, ['Chờ', 'Đang chat'])) {
    $existingMessages = $chatModel->getMessages($maPhien);
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

    <!-- Redesigned customer support with improved layout structure -->
    <div class="chat-wrapper customer-chat-wrapper">
        <div class="chat-container customer-chat-container">
            <!-- Main Chat Area -->
            <main class="chat-main customer-chat-main">
                <!-- Chat Header -->
                <header class="chat-header customer-header">
                    <div class="header-content">
                        <h2 class="header-title">
                            <i class="fas fa-headset"></i>
                            Hỗ trợ Khách Hàng
                        </h2>
                        <p class="header-subtitle">Chúng tôi sẵn sàng giúp bạn 24/7</p>
                    </div>
                    <div class="header-actions">
                        <div class="status-indicator-header">
                            <span class="status-dot" id="statusDot"></span>
                            <span class="status-text" id="statusText">Chờ kết nối</span>
                        </div>
                        <button id="closeSessionBtn" class="btn-end-session" title="Kết thúc phiên chat">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </div>
                </header>

                <!-- Messages Container -->
                <div class="messages-container" id="chatMessages">
                    <!-- Load existing messages if session is active, otherwise show welcome -->
                    <?php if (!empty($existingMessages)): ?>
                        <?php foreach ($existingMessages as $msg): ?>
                            <?php
                                $isCurrentUser = $msg['nguoiGui'] == $maNguoiDung;
                                $messageClass = $isCurrentUser ? 'message-sent' : 'message-received';
                                $senderName = $msg['tenNguoiDung'] ?: ($msg['tenNhanVien'] ?: 'Nhân viên hỗ trợ');
                                $senderRole = $msg['vaiTroNguoiGui'] ?: 'Nhân viên';
                                $roleClass = '';
                                switch($senderRole) {
                                    case 'Tài xế': $roleClass = 'driver'; break;
                                    case 'Nhân viên': $roleClass = 'staff'; break;
                                    case 'Khách hàng': $roleClass = 'customer'; break;
                                    case 'Quản trị viên': $roleClass = 'admin'; break;
                                    default: $roleClass = 'customer';
                                }
                            ?>
                            <div class="message-wrapper <?php echo $messageClass; ?>">
                                <div class="message-avatar">
                                    <?php if ($msg['avt'] && trim($msg['avt']) !== ''): ?>
                                        <img src="<?php echo BASE_URL . '/' . $msg['avt']; ?>" alt="<?php echo htmlspecialchars($senderName); ?>" class="avatar-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="avatar-circle <?php echo $roleClass; ?>" style="display: none;"><?php echo strtoupper(substr($senderName, 0, 1)); ?></div>
                                    <?php else: ?>
                                        <div class="avatar-circle <?php echo $roleClass; ?>"><?php echo strtoupper(substr($senderName, 0, 1)); ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="message-content">
                                    <div class="message-header">
                                        <span class="message-sender"><?php echo htmlspecialchars($senderName); ?></span>
                                        <span class="role-badge <?php echo $roleClass; ?>"><?php echo htmlspecialchars($senderRole); ?></span>
                                        <span class="message-time"><?php echo date('H:i', strtotime($msg['ngayTao'])); ?></span>
                                    </div>
                                    <div class="message-bubble">
                                        <div class="message-text"><?php echo htmlspecialchars($msg['noiDung']); ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Welcome message shown only if no active session -->
                        <div class="welcome-message">
                            <div class="welcome-icon">
                                <i class="fas fa-comments"></i>
                            </div>
                            <h3>Xin chào!</h3>
                            <p class="welcome-description">
                                Chào mừng bạn đến với dịch vụ hỗ trợ khách hàng XeGoo. Đội ngũ nhân viên hỗ trợ của chúng tôi luôn sẵn sàng giúp bạn giải quyết mọi vấn đề liên quan đến dịch vụ đặt xe.
                            </p>
                            
                            <!-- Added suggested quick-reply messages section -->
                            <div class="suggested-messages">
                                <p class="suggested-title">Bạn cần giúp đỡ về vấn đề gì?</p>
                                <div class="suggested-buttons">
                                    <button class="suggested-btn" onclick="sendSuggestedMessage('Tìm kiếm chuyến xe')">
                                        <i class="fas fa-search"></i>
                                        <span>Tìm kiếm chuyến xe</span>
                                    </button>
                                    <button class="suggested-btn" onclick="sendSuggestedMessage('Thanh toán & hoàn tiền')">
                                        <i class="fas fa-wallet"></i>
                                        <span>Thanh toán & hoàn tiền</span>
                                    </button>
                                    <button class="suggested-btn" onclick="sendSuggestedMessage('Vấn đề với tài xế')">
                                        <i class="fas fa-car"></i>
                                        <span>Vấn đề với tài xế</span>
                                    </button>
                                    <button class="suggested-btn" onclick="sendSuggestedMessage('Báo cáo sự cố')">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <span>Báo cáo sự cố</span>
                                    </button>
                                    <button class="suggested-btn" onclick="sendSuggestedMessage('Hỏi về tính năng ứng dụng')">
                                        <i class="fas fa-question-circle"></i>
                                        <span>Hỏi về tính năng ứng dụng</span>
                                    </button>
                                    <button class="suggested-btn" onclick="sendSuggestedMessage('Khác')">
                                        <i class="fas fa-ellipsis-h"></i>
                                        <span>Khác</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
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
        let maPhien = <?php echo $maPhien ? $maPhien : 'null'; ?>;
        let lastMessageId = <?php echo !empty($existingMessages) ? max(array_column($existingMessages, 'maTinNhan')) : 0; ?>;
        let sessionCreated = <?php echo $maPhien ? 'true' : 'false'; ?>;

        document.getElementById('sendBtn').addEventListener('click', sendMessage);
        document.getElementById('messageInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
        document.getElementById('closeSessionBtn').addEventListener('click', closeSession);

        function getRoleBadgeClass(vaiTro) {
            switch(vaiTro) {
                case 'Tài xế': return 'driver';
                case 'Nhân viên': return 'staff';
                case 'Khách hàng': return 'customer';
                case 'Quản trị viên': return 'admin';
                default: return 'customer';
            }
        }

        function sendSuggestedMessage(message) {
            document.getElementById('messageInput').value = message;
            sendMessage();
        }

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
            const roleClass = getRoleBadgeClass(senderRole);
            
            let avatarHtml = '';
            if (msg.avt && msg.avt.trim() !== '') {
                avatarHtml = `<img src="${baseUrl}/${msg.avt}" alt="${senderName}" class="avatar-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                             <div class="avatar-circle ${roleClass}" style="display: none;">${senderName.charAt(0).toUpperCase()}</div>`;
            } else {
                avatarHtml = `<div class="avatar-circle ${roleClass}">${senderName.charAt(0).toUpperCase()}</div>`;
            }
            
            const messageHtml = `
                <div class="message-wrapper ${messageClass}">
                    <div class="message-avatar">
                        ${avatarHtml}
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

        if (sessionCreated && maPhien) {
            loadMessages();
        }

        setInterval(loadMessages, 2000);
    </script>
</body>
</html>
