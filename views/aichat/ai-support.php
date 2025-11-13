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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/chatAI.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<style>
.ai-welcome-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  background: #f9fbff;
  font-family: "Inter", "Segoe UI", sans-serif;
  color: #2d3748;
  font-size: 12px;
}

.ai-welcome-card {
  max-width: 460px;
  background: #fff;
  padding: 16px 20px;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  text-align: center;
}

.ai-welcome-header {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  margin-bottom: 12px;
}

.ai-welcome-avatar {
  width: 42px;
  height: 42px;
  border-radius: 50%;
}

.ai-welcome-text h2 {
  font-size: 14px;
  font-weight: 700;
  color: #1a237e;
  margin: 0;
}

.ai-welcome-text p {
  color: #4a5568;
  margin: 2px 0 0 0;
  font-size: 12px;
}

.ai-welcome-intro {
  margin: 10px 0 12px;
  font-size: 12px;
  line-height: 1.5;
  color: #555;
}

.ai-welcome-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
  margin: 12px auto 16px;
  max-width: 360px;
}

.ai-item {
  background: #f5f8ff;
  border: 1px solid #dee7ff;
  border-radius: 8px;
  padding: 6px 8px;
  color: #2c3e50;
  text-align: left;
  transition: all 0.2s ease;
}

.ai-item:hover {
  background: #eaf1ff;
  transform: translateY(-1px);
  border-color: #c4d4ff;
}

.ai-welcome-footer {
  font-size: 12px;
  color: #3b4cca;
  font-weight: 500;
  margin-top: 4px;
}
</style>
<body>
     <?php require_once __DIR__ . '/../layouts/header.php'; ?>
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
            <!-- Welcome message now centered and with improved layout -->
            <div class="ai-welcome-wrapper">
  <div class="ai-welcome-card">
    <div class="ai-welcome-header">
      <img src="https://cdn-icons-png.flaticon.com/512/4712/4712109.png" alt="AI bot" class="ai-welcome-avatar">
      <div class="ai-welcome-text">
        <h2>Xin chào</h2>
        <p>Tôi là <strong>trợ lý AI của XeGoo</strong> – luôn sẵn sàng giúp bạn!</p>
      </div>
    </div>

    <p class="ai-welcome-intro">
      Tôi có thể hỗ trợ bạn trong việc tra cứu thông tin, đặt vé và giải đáp các thắc mắc liên quan đến dịch vụ của XeGoo.  
      Dưới đây là những việc tôi có thể giúp bạn:
    </p>

    <div class="ai-welcome-grid">
      <div class="ai-item">🚌 Tìm kiếm & đặt vé chuyến xe</div>
      <div class="ai-item">💳 Thanh toán & hoàn tiền</div>
      <div class="ai-item">📘 Hướng dẫn sử dụng hệ thống</div>
      <div class="ai-item">🎟️ Khuyến mãi & mã giảm giá</div>
      <div class="ai-item">⭐ Tra cứu điểm tích lũy & ưu đãi</div>
    </div>

    <p class="ai-welcome-footer">
      Hãy gửi câu hỏi hoặc yêu cầu của bạn — tôi sẽ phản hồi ngay!
    </p>
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
            if (content.includes('<div') || content.includes('<p')) {
                return content;
            }
            
            const lines = content.split('\n');
            let formatted = '<div class="ai-response-content">';
            let currentSection = null;
            
            for (let i = 0; i < lines.length; i++) {
                const line = lines[i].trim();
                
                if (!line) continue;
                
                // Detect trip information blocks
                if (line.includes('Chuyến #') || line.includes('Loại xe:') || line.includes('Giờ khởi hành:') || line.includes('━')) {
                    if (currentSection === 'trip') {
                        formatted += '</div>';
                    }
                    if (line.includes('━')) {
                        formatted += '</div>';
                        currentSection = null;
                    } else {
                        formatted += '<div class="ai-trip-info">';
                        currentSection = 'trip';
                        formatted += line.replace(/🚌|💰|⏰|📋|🌟|📞|💬|📧|🔗/g, '') + '<br>';
                    }
                } else if (currentSection === 'trip' && line.length > 0) {
                    formatted += line.replace(/🚌|💰|⏰|📋|🌟|📞|💬|📧|🔗/g, '') + '<br>';
                } else if (line.startsWith('###') || line.startsWith('##') || line.startsWith('#')) {
                    const title = line.replace(/^#+\s*/, '').replace(/🚌|💰|⏰|📋|🌟|📞|💬|📧|🔗/g, '');
                    formatted += `<div class="ai-response-section"><div class="ai-response-title">${title}</div>`;
                } else if (line.startsWith('-') || line.startsWith('•')) {
                    const item = line.replace(/^[-•]\s*/, '').replace(/🚌|💰|⏰|📋|🌟|📞|💬|📧|🔗/g, '');
                    formatted += `<div class="ai-response-item">${item}</div>`;
                } else if (line.includes('[') && line.includes('](')) {
                    const linkMatch = line.match(/\[(.*?)\]$$(.*?)$$/);
                    if (linkMatch) {
                        const linkText = linkMatch[1];
                        const linkUrl = linkMatch[2];
                        formatted += `<p><a class="ai-response-link" href="${linkUrl}" target="_blank">${linkText}</a></p>`;
                    } else {
                        formatted += `<p>${line}</p>`;
                    }
                } else {
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
            
            const welcomeWrapper = chatMessages.querySelector('.ai-welcome-wrapper');
            if (welcomeWrapper) {
                welcomeWrapper.remove();
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
