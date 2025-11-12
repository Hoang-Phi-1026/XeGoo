<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/xegoo');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hỗ trợ AI - XeGoo</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/main.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/chat.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Override chat styles specifically for AI chat page to match customer support design */
        .ai-chat-wrapper {
            position: relative;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .ai-chat-container {
            display: flex;
            flex-direction: column;
            height: 100%;
            background: #f8f9fa;
            max-width: 100%;
        }
        
        .ai-chat-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .ai-chat-header-content {
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }
        
        .ai-chat-title {
            font-size: 28px;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .ai-chat-subtitle {
            font-size: 14px;
            opacity: 0.9;
            margin: 8px 0 0 0;
        }
        
        .ai-messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        
        .ai-message-wrapper {
            display: flex;
            gap: 12px;
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .ai-message-wrapper.user {
            justify-content: flex-end;
        }
        
        .ai-message-wrapper.ai {
            justify-content: flex-start;
        }
        
        .ai-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: white;
            font-size: 16px;
            flex-shrink: 0;
        }
        
        .ai-avatar.user {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .ai-avatar.ai {
            background: #6c757d;
        }
        
        .ai-message-bubble {
            max-width: 70%;
            padding: 16px;
            border-radius: 12px;
            line-height: 1.6;
            word-wrap: break-word;
        }
        
        .ai-message-wrapper.user .ai-message-bubble {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-bottom-right-radius: 4px;
        }
        
        .ai-message-wrapper.ai .ai-message-bubble {
            background: white;
            color: #333;
            border: 1px solid #ddd;
            border-bottom-left-radius: 4px;
        }
        
        /* Style for formatted AI response content */
        .ai-response-content {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .ai-response-section {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .ai-response-title {
            font-weight: 600;
            font-size: 15px;
            color: inherit;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .ai-response-item {
            font-size: 14px;
            padding-left: 20px;
            position: relative;
            color: inherit;
        }
        
        .ai-response-item:before {
            content: "•";
            position: absolute;
            left: 0;
            font-weight: bold;
        }
        
        .ai-response-link {
            color: inherit;
            text-decoration: underline;
            cursor: pointer;
        }
        
        .ai-message-wrapper.ai .ai-response-link {
            color: #667eea;
        }
        
        .ai-message-wrapper.user .ai-response-link {
            color: white;
        }
        
        .ai-trip-info {
            background: rgba(102, 126, 234, 0.1);
            padding: 12px;
            border-left: 3px solid #667eea;
            border-radius: 4px;
            margin: 8px 0;
            font-size: 14px;
        }
        
        .ai-message-wrapper.user .ai-trip-info {
            background: rgba(255, 255, 255, 0.2);
            border-left-color: white;
        }
        
        .ai-trip-info-row {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            margin: 4px 0;
        }
        
        .ai-trip-info-label {
            font-weight: 500;
            flex-shrink: 0;
        }
        
        .ai-trip-info-value {
            text-align: right;
            flex: 1;
        }
        
        .ai-typing-indicator {
            display: flex;
            gap: 4px;
            padding: 16px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 12px;
            width: fit-content;
        }
        
        .ai-typing-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #667eea;
            animation: bounce 1.4s infinite;
        }
        
        .ai-typing-dot:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .ai-typing-dot:nth-child(3) {
            animation-delay: 0.4s;
        }
        
        @keyframes bounce {
            0%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
        }
        
        .ai-footer {
            padding: 16px 24px;
            background: white;
            border-top: 1px solid #ddd;
            display: flex;
            gap: 8px;
        }
        
        .ai-input-group {
            flex: 1;
            display: flex;
            gap: 8px;
        }
        
        .ai-input-group input {
            flex: 1;
            border: 1px solid #ddd;
            border-radius: 24px;
            padding: 12px 16px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s;
        }
        
        .ai-input-group input:focus {
            border-color: #667eea;
        }
        
        .ai-send-btn {
            border: none;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: transform 0.2s;
            flex-shrink: 0;
        }
        
        .ai-send-btn:hover {
            transform: scale(1.05);
        }
        
        .ai-send-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .ai-action-buttons {
            display: flex;
            gap: 8px;
            padding: 12px 24px;
            background: white;
            border-top: 1px solid #ddd;
            justify-content: center;
        }
        
        .ai-action-btn {
            padding: 12px 16px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.3s;
        }
        
        .ai-action-btn:hover {
            background: #f0f0f0;
            border-color: #667eea;
        }
        
        .ai-action-btn.primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: transparent;
        }
        
        @media (max-width: 768px) {
            .ai-message-bubble {
                max-width: 85%;
            }
            
            .ai-chat-title {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Redesigned AI support page to match professional customer support design -->
    <div class="ai-chat-wrapper">
        <header class="ai-chat-header">
            <div class="ai-chat-header-content">
                <h1 class="ai-chat-title">
                    <i class="fas fa-robot"></i>
                    Hỗ trợ AI XeGoo
                </h1>
                <p class="ai-chat-subtitle">Hỏi tôi bất cứ điều gì về dịch vụ đặt xe của chúng tôi</p>
            </div>
        </header>
        
        <div class="ai-messages-container" id="chatMessages">
            <div class="ai-message-wrapper ai">
                <div class="ai-avatar ai">🤖</div>
                <div class="ai-message-bubble">
                    <div class="ai-response-content">
                        <p>Xin chào! Tôi là trợ lý AI của XeGoo. Tôi có thể giúp bạn với:</p>
                        <div class="ai-response-section">
                            <div class="ai-response-item">Tìm kiếm và đặt vé chuyến xe</div>
                            <div class="ai-response-item">Thông tin về thanh toán và hoàn tiền</div>
                            <div class="ai-response-item">Hướng dẫn sử dụng dịch vụ</div>
                            <div class="ai-response-item">Khuyến mãi và mã giảm giá</div>
                            <div class="ai-response-item">Điểm tích lũy và ưu đãi</div>
                        </div>
                        <p>Hãy hỏi tôi bất cứ điều gì!</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="ai-footer">
            <div class="ai-input-group">
                <input 
                    type="text" 
                    id="messageInput" 
                    placeholder="Nhập câu hỏi của bạn..." 
                    autocomplete="off"
                >
                <button id="sendBtn" class="ai-send-btn" title="Gửi">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
        
        <div class="ai-action-buttons">
            <button class="ai-action-btn" onclick="switchToStaffChat()">
                <i class="fas fa-headset"></i> Trò chuyện với Nhân viên
            </button>
            <button class="ai-action-btn" onclick="location.href='<?php echo BASE_URL; ?>'">
                <i class="fas fa-home"></i> Về Trang Chủ
            </button>
        </div>
    </div>
    
    <script>
        const messageInput = document.getElementById('messageInput');
        const chatMessages = document.getElementById('chatMessages');
        const sendBtn = document.getElementById('sendBtn');
        const baseUrl = '<?php echo BASE_URL; ?>';
        
        messageInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
        
        sendBtn.addEventListener('click', sendMessage);
        
        function formatAIResponse(content) {
            // If content already has HTML structure, return as is
            if (content.includes('<div') || content.includes('<p')) {
                return content;
            }
            
            // Split by newlines and format
            const lines = content.split('\n');
            let formatted = '<div class="ai-response-content">';
            let currentSection = null;
            
            for (let i = 0; i < lines.length; i++) {
                const line = lines[i].trim();
                
                if (!line) continue;
                
                // Detect trip information blocks (contain "Chuyến #" or emoji)
                if (line.includes('Chuyến #') || line.includes('Loại xe:') || line.includes('Giờ khởi hành:')) {
                    if (currentSection) {
                        formatted += '</div>';
                    }
                    formatted += '<div class="ai-trip-info">';
                    currentSection = 'trip';
                    formatted += line.replace(/🚌|💰|⏰|📋|🌟|📞|💬|📧|🔗/g, '') + '<br>';
                } else if (currentSection === 'trip') {
                    // Continue trip info
                    if (line.includes('━')) {
                        formatted += '</div>';
                        currentSection = null;
                    } else if (line.length > 0) {
                        formatted += line.replace(/🚌|💰|⏰|📋|🌟|📞|💬|📧|🔗/g, '') + '<br>';
                    }
                } else if (line.startsWith('###') || line.startsWith('##') || line.startsWith('#')) {
                    // Section headers
                    const title = line.replace(/^#+\s*/, '').replace(/🚌|💰|⏰|📋|🌟|📞|💬|📧|🔗/g, '');
                    formatted += `<div class="ai-response-section"><div class="ai-response-title">${title}</div>`;
                } else if (line.startsWith('-') || line.startsWith('•')) {
                    // Bullet points
                    const item = line.replace(/^[-•]\s*/, '').replace(/🚌|💰|⏰|📋|🌟|📞|💬|📧|🔗/g, '');
                    formatted += `<div class="ai-response-item">${item}</div>`;
                } else if (line.includes('[') && line.includes('](')) {
                    // Markdown links
                    const linkMatch = line.match(/\[(.*?)\]$$(.*?)$$/);
                    if (linkMatch) {
                        const linkText = linkMatch[1];
                        const linkUrl = linkMatch[2];
                        formatted += `<p><a class="ai-response-link" href="${linkUrl}" target="_blank">${linkText}</a></p>`;
                    } else {
                        formatted += `<p>${line}</p>`;
                    }
                } else {
                    // Regular text
                    formatted += `<p>${line}</p>`;
                }
            }
            
            if (currentSection) {
                formatted += '</div>';
            }
            formatted += '</div>';
            
            return formatted;
        }
        
        function addMessage(content, isUser = false) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'ai-message-wrapper ' + (isUser ? 'user' : 'ai');
            
            const avatarDiv = document.createElement('div');
            avatarDiv.className = 'ai-avatar ' + (isUser ? 'user' : 'ai');
            avatarDiv.textContent = isUser ? '👤' : '🤖';
            
            const bubbleDiv = document.createElement('div');
            bubbleDiv.className = 'ai-message-bubble';
            
            if (isUser) {
                bubbleDiv.textContent = content;
            } else {
                bubbleDiv.innerHTML = formatAIResponse(content);
            }
            
            if (isUser) {
                messageDiv.appendChild(bubbleDiv);
                messageDiv.appendChild(avatarDiv);
            } else {
                messageDiv.appendChild(avatarDiv);
                messageDiv.appendChild(bubbleDiv);
            }
            
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        function showTyping() {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'ai-message-wrapper ai';
            messageDiv.id = 'typing-indicator';
            
            const avatarDiv = document.createElement('div');
            avatarDiv.className = 'ai-avatar ai';
            avatarDiv.textContent = '🤖';
            
            const typingDiv = document.createElement('div');
            typingDiv.className = 'ai-typing-indicator';
            typingDiv.innerHTML = '<span class="ai-typing-dot"></span><span class="ai-typing-dot"></span><span class="ai-typing-dot"></span>';
            
            messageDiv.appendChild(avatarDiv);
            messageDiv.appendChild(typingDiv);
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        function removeTyping() {
            const typingDiv = document.getElementById('typing-indicator');
            if (typingDiv) {
                typingDiv.remove();
            }
        }
        
        async function sendMessage() {
            const message = messageInput.value.trim();
            
            if (!message) {
                return;
            }
            
            addMessage(message, true);
            messageInput.value = '';
            messageInput.focus();
            
            sendBtn.disabled = true;
            showTyping();
            
            try {
                const response = await fetch(baseUrl + '/api/aichat/ask', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ message: message })
                });
                
                const data = await response.json();
                removeTyping();
                
                if (data.error) {
                    addMessage('❌ ' + data.error, false);
                } else if (data.reply) {
                    addMessage(data.reply, false);
                }
            } catch (error) {
                removeTyping();
                console.error('Error:', error);
                addMessage('❌ Có lỗi xảy ra khi gửi tin nhắn. Vui lòng thử lại.', false);
            } finally {
                sendBtn.disabled = false;
            }
        }
        
        async function switchToStaffChat() {
            try {
                const response = await fetch(baseUrl + '/api/aichat/switch-to-staff', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                });
                
                const data = await response.json();
                
                if (data.requireLogin) {
                    window.location.href = baseUrl + '/login?return_url=' + encodeURIComponent(baseUrl + '/support');
                } else if (data.success) {
                    window.location.href = data.redirectUrl;
                } else if (data.error) {
                    alert('❌ ' + data.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('❌ Có lỗi xảy ra. Vui lòng thử lại.');
            }
        }
        
        window.addEventListener('load', function() {
            messageInput.focus();
        });
    </script>
</body>
</html>
