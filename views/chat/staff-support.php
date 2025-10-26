<?php
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 2) {
    header('Location: ' . BASE_URL . '/');
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hỗ trợ hành khách - XeGoo</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/main.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/chat.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <!-- Redesigned staff support with modern two-column layout -->
    <div class="chat-wrapper staff-chat-wrapper">
        <div class="chat-container staff-chat-container">
            <!-- Left Sidebar - Conversations List -->
            <aside class="conversations-sidebar">
                <div class="sidebar-header">
                    <h3 class="sidebar-title">
                        <i class="fas fa-comments"></i>
                        Tin Nhắn
                    </h3>
                    <span class="pending-badge" id="pendingCount">0</span>
                </div>

                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input 
                        type="text" 
                        id="searchInput" 
                        class="search-input" 
                        placeholder="Tìm kiếm..."
                    >
                </div>

                <div class="conversations-list" id="sessionsList">
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Không có tin nhắn</p>
                    </div>
                </div>
            </aside>

            <!-- Main Chat Area -->
            <main class="chat-main staff-chat-main">
                <!-- Empty State -->
                <div class="chat-empty-state" id="emptyState">
                    <div class="empty-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h3>Chọn một cuộc hội thoại</h3>
                    <p>Chọn khách hàng từ danh sách để bắt đầu trò chuyện</p>
                </div>

                <!-- Chat Content (Hidden by default) -->
                <div class="chat-content" id="chatContent" style="display: none;">
                    <!-- Chat Header -->
                    <header class="chat-header staff-header">
                        <div class="header-info">
                            <div class="customer-avatar">
                                <!-- Updated to use actual avatar or initials -->
                                <img id="customerAvatarImg" src="/placeholder.svg" alt="Avatar" class="avatar-img" style="display: none;">
                                <div class="avatar-circle" id="customerAvatar">U</div>
                            </div>
                            <div class="header-text">
                                <h2 class="header-title" id="chatTitle">Khách hàng</h2>
                                <div class="header-subtitle-group">
                                    <p class="header-subtitle" id="chatSubtitle">0123456789</p>
                                    <!-- Added role badge in header -->
                                    <span class="role-badge" id="headerRoleBadge">Khách hàng</span>
                                </div>
                            </div>
                        </div>
                        <div class="header-actions">
                            <button id="closeSessionBtn" class="btn-icon" title="Đóng phiên">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </header>

                    <!-- Messages Container -->
                    <div class="messages-container" id="chatMessages"></div>

                    <!-- Input Area -->
                    <footer class="chat-footer">
                        <div class="input-group">
                            <input 
                                type="text" 
                                id="messageInput" 
                                class="message-input" 
                                placeholder="Nhập tin nhắn..."
                                autocomplete="off"
                            >
                            <button id="sendBtn" class="btn-send" title="Gửi">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </footer>
                </div>
            </main>
        </div>
    </div>

    <script>
        const baseUrl = '<?php echo BASE_URL; ?>';
        const currentUserId = <?php echo $_SESSION['user_id']; ?>;
        let currentSession = null;
        let lastMessageId = 0;
        let allSessions = [];

        document.getElementById('sendBtn').addEventListener('click', sendMessage);
        document.getElementById('messageInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
        document.getElementById('closeSessionBtn').addEventListener('click', closeSession);
        document.getElementById('searchInput').addEventListener('input', filterSessions);

        function getAvatarHtml(user, size = 'medium') {
            const sizeClass = size === 'small' ? 'avatar-sm' : size === 'large' ? 'avatar-lg' : '';
            
            if (user.avt && user.avt.trim() !== '') {
                // User has an avatar image
                const avatarPath = baseUrl + '/' + user.avt;
                return `<img src="${avatarPath}" alt="${user.tenNguoiDung}" class="avatar-img ${sizeClass}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="avatar-circle ${sizeClass}" style="display: none;">${user.tenNguoiDung.charAt(0).toUpperCase()}</div>`;
            } else {
                // No avatar, show initials
                return `<div class="avatar-circle ${sizeClass}">${user.tenNguoiDung.charAt(0).toUpperCase()}</div>`;
            }
        }

        function getRoleBadgeClass(maVaiTro) {
            switch(parseInt(maVaiTro)) {
                case 1: return 'admin';
                case 2: return 'staff';
                case 3: return 'driver';
                case 4: return 'customer';
                default: return 'customer';
            }
        }

        function getRoleName(maVaiTro) {
            switch(parseInt(maVaiTro)) {
                case 1: return 'Quản trị viên';
                case 2: return 'Nhân viên';
                case 3: return 'Tài xế';
                case 4: return 'Khách hàng';
                default: return 'Khách hàng';
            }
        }

        function loadSessions() {
            fetch(baseUrl + '/api/chat/get-pending-sessions')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        allSessions = data.sessions;
                        updateSessionsList(data.sessions);
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function updateSessionsList(sessions) {
            const pendingCount = sessions.length;
            document.getElementById('pendingCount').textContent = pendingCount;

            const sessionsList = document.getElementById('sessionsList');
            
            if (pendingCount === 0) {
                sessionsList.innerHTML = '<div class="empty-state"><i class="fas fa-inbox"></i><p>Không có tin nhắn</p></div>';
                return;
            }

            sessionsList.innerHTML = '';
            sessions.forEach(session => {
                const userRole = getRoleName(session.maVaiTro);
                const roleClass = getRoleBadgeClass(session.maVaiTro);
                
                const sessionHtml = `
                    <div class="conversation-item" data-session-id="${session.maPhien}">
                        <div class="conversation-avatar">
                            ${getAvatarHtml(session, 'small')}
                            ${session.unreadCount > 0 ? `<span class="unread-badge">${session.unreadCount}</span>` : ''}
                        </div>
                        <div class="conversation-info">
                            <div class="conversation-header">
                                <h4 class="conversation-name">${session.tenNguoiDung || 'Ẩn danh'}</h4>
                                <span class="conversation-time">${new Date(session.ngayCapNhat).toLocaleTimeString('vi-VN', {hour: '2-digit', minute: '2-digit'})}</span>
                            </div>
                            <div class="conversation-meta">
                                <span class="role-badge ${roleClass}">${userRole}</span>
                                <span class="phone-text">${session.soDienThoai || 'N/A'}</span>
                            </div>
                        </div>
                    </div>
                `;
                sessionsList.insertAdjacentHTML('beforeend', sessionHtml);
            });

            document.querySelectorAll('.conversation-item').forEach(item => {
                item.addEventListener('click', function() {
                    selectSession(this.dataset.sessionId);
                });
            });
        }

        function filterSessions() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const filtered = allSessions.filter(session => 
                session.tenNguoiDung.toLowerCase().includes(searchTerm) ||
                session.soDienThoai.includes(searchTerm)
            );
            updateSessionsList(filtered);
        }

        function selectSession(maPhien) {
            currentSession = maPhien;
            lastMessageId = 0;

            document.querySelectorAll('.conversation-item').forEach(item => {
                item.classList.remove('active');
            });
            document.querySelector(`[data-session-id="${maPhien}"]`).classList.add('active');

            fetch(baseUrl + '/api/chat/get-messages?maPhien=' + maPhien)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const session = allSessions.find(s => s.maPhien == maPhien);
                        document.getElementById('chatTitle').textContent = session.tenNguoiDung || 'Ẩn danh';
                        document.getElementById('chatSubtitle').textContent = session.soDienThoai || 'N/A';
                        
                        const avatarImg = document.getElementById('customerAvatarImg');
                        const avatarCircle = document.getElementById('customerAvatar');
                        
                        if (session.avt && session.avt.trim() !== '') {
                            avatarImg.src = baseUrl + '/' + session.avt;
                            avatarImg.style.display = 'block';
                            avatarCircle.style.display = 'none';
                        } else {
                            avatarImg.style.display = 'none';
                            avatarCircle.style.display = 'flex';
                            avatarCircle.textContent = session.tenNguoiDung ? session.tenNguoiDung.charAt(0).toUpperCase() : 'U';
                        }
                        
                        const roleName = getRoleName(session.maVaiTro);
                        document.getElementById('headerRoleBadge').textContent = roleName;
                        document.getElementById('headerRoleBadge').className = 'role-badge ' + getRoleBadgeClass(session.maVaiTro);
                        
                        displayMessages(data.messages);
                        document.getElementById('emptyState').style.display = 'none';
                        document.getElementById('chatContent').style.display = 'flex';

                        fetch(baseUrl + '/api/chat/assign-staff', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ maPhien: maPhien })
                        });
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function displayMessages(messages) {
            document.getElementById('chatMessages').innerHTML = '';
            messages.forEach(msg => {
                const isCurrentUser = msg.nguoiGui == currentUserId;
                addMessageToUI(msg, isCurrentUser);
                lastMessageId = msg.maTinNhan;
            });
            scrollToBottom();
        }

        function addMessageToUI(msg, isCurrentUser) {
            const messageClass = isCurrentUser ? 'message-sent' : 'message-received';
            const senderName = msg.tenNguoiDung || msg.tenNhanVien || 'Ẩn danh';
            const senderRole = msg.vaiTroNguoiGui || 'Khách hàng';
            const roleClass = getRoleBadgeClass(msg.vaiTroNguoiGui);
            
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

        function sendMessage() {
            if (!currentSession) return;

            const noiDung = document.getElementById('messageInput').value.trim();
            if (!noiDung) return;

            document.getElementById('messageInput').value = '';

            fetch(baseUrl + '/api/chat/send-message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    maPhien: currentSession,
                    noiDung: noiDung
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadMessages();
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function loadMessages() {
            if (!currentSession) return;

            fetch(baseUrl + '/api/chat/get-messages?maPhien=' + currentSession + '&lastMessageId=' + lastMessageId)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.messages.length > 0) {
                        data.messages.forEach(msg => {
                            addMessageToUI(msg, msg.nguoiGui == currentUserId);
                            lastMessageId = msg.maTinNhan;
                        });
                        scrollToBottom();
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function scrollToBottom() {
            const container = document.getElementById('chatMessages');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        }

        function closeSession() {
            if (confirm('Bạn có chắc chắn muốn đóng phiên chat này?')) {
                fetch(baseUrl + '/api/chat/close-session', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ maPhien: currentSession })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        currentSession = null;
                        document.getElementById('chatContent').style.display = 'none';
                        document.getElementById('emptyState').style.display = 'flex';
                        loadSessions();
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        }

        setInterval(loadSessions, 3000);
        setInterval(loadMessages, 2000);

        loadSessions();
    </script>
</body>
</html>
