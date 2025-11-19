/**
 * Driver Reminder Popup System
 * Polls for unread notifications and displays popups for trip reminders
 */

class DriverReminderPopup {
    constructor() {
        this.pollInterval = 30000; // Poll every 30 seconds
        this.displayedNotifications = new Set();
        this.init();
    }
    
    init() {
        console.log('[v0] Initializing driver reminder popup system');
        this.startPolling();
        this.setupStyles();
    }
    
    startPolling() {
        // Initial check
        this.checkNotifications();
        
        // Poll regularly
        this.pollTimer = setInterval(() => {
            this.checkNotifications();
        }, this.pollInterval);
    }
    
    stopPolling() {
        if (this.pollTimer) {
            clearInterval(this.pollTimer);
        }
    }
    
    checkNotifications() {
        fetch('/api/driver/notifications/unread')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.notifications && data.notifications.length > 0) {
                    data.notifications.forEach(notification => {
                        if (!this.displayedNotifications.has(notification.maThongBao)) {
                            this.showReminderPopup(notification);
                            this.displayedNotifications.add(notification.maThongBao);
                        }
                    });
                }
            })
            .catch(error => console.error('[v0] Error checking notifications:', error));
    }
    
    showReminderPopup(notification) {
        console.log('[v0] Showing reminder popup for trip:', notification.maChuyenXe);
        
        const popup = document.createElement('div');
        popup.className = 'driver-reminder-popup';
        popup.innerHTML = `
            <div class="popup-container">
                <div class="popup-header">
                    <div class="popup-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h3>${notification.tieu_de}</h3>
                    <button class="popup-close" onclick="this.closest('.driver-reminder-popup').remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="popup-body">
                    <p class="popup-message">${notification.noi_dung}</p>
                    
                    <div class="trip-info-box">
                        <div class="info-row">
                            <i class="fas fa-clock"></i>
                            <span>Khởi hành lúc: <strong>${this.formatTime(notification.thoiGianKhoiHanh)}</strong></span>
                        </div>
                    </div>
                    
                    <div class="popup-actions">
                        <button class="btn-primary" onclick="window.location.href = '/driver/report'">
                            <i class="fas fa-arrow-right"></i>
                            Xem chuyến xe
                        </button>
                        <button class="btn-secondary" onclick="driverReminderPopup.markAsRead('${notification.maThongBao}')">
                            <i class="fas fa-check"></i>
                            Đã biết
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(popup);
        
        // Auto-remove after 10 seconds
        setTimeout(() => {
            if (popup.parentElement) {
                popup.remove();
            }
        }, 10000);
    }
    
    markAsRead(maThongBao) {
        fetch('/api/driver/notifications/mark-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ maThongBao })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('[v0] Marked notification as read');
                document.querySelectorAll('.driver-reminder-popup').forEach(p => p.remove());
            }
        })
        .catch(error => console.error('[v0] Error marking as read:', error));
    }
    
    formatTime(datetime) {
        const date = new Date(datetime);
        return date.toLocaleString('vi-VN', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    setupStyles() {
        const style = document.createElement('style');
        style.textContent = `
            .driver-reminder-popup {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                animation: slideIn 0.3s ease-out;
            }
            
            @keyframes slideIn {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            .popup-container {
                background: white;
                border-radius: 8px;
                box-shadow: 0 4px 20px rgba(255, 107, 53, 0.3);
                border-left: 5px solid #FF6B35;
                max-width: 400px;
                overflow: hidden;
            }
            
            .popup-header {
                background: linear-gradient(135deg, #FF6B35 0%, #FF8C5A 100%);
                color: white;
                padding: 16px;
                display: flex;
                align-items: flex-start;
                gap: 12px;
            }
            
            .popup-icon {
                font-size: 24px;
                animation: pulse 1.5s infinite;
            }
            
            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.6; }
            }
            
            .popup-header h3 {
                flex: 1;
                margin: 0;
                font-size: 16px;
            }
            
            .popup-close {
                background: none;
                border: none;
                color: white;
                cursor: pointer;
                font-size: 18px;
                padding: 0;
                line-height: 1;
            }
            
            .popup-body {
                padding: 16px;
            }
            
            .popup-message {
                margin: 0 0 12px 0;
                font-size: 14px;
                line-height: 1.5;
                color: #333;
            }
            
            .trip-info-box {
                background: #FFF3E0;
                padding: 12px;
                border-radius: 4px;
                margin-bottom: 16px;
            }
            
            .info-row {
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 14px;
                color: #666;
            }
            
            .info-row i {
                color: #FF6B35;
                width: 20px;
                text-align: center;
            }
            
            .info-row strong {
                color: #333;
            }
            
            .popup-actions {
                display: flex;
                gap: 8px;
            }
            
            .btn-primary, .btn-secondary {
                flex: 1;
                padding: 10px 12px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 13px;
                font-weight: 500;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
                transition: all 0.2s;
            }
            
            .btn-primary {
                background: #FF6B35;
                color: white;
            }
            
            .btn-primary:hover {
                background: #E55A25;
            }
            
            .btn-secondary {
                background: #f0f0f0;
                color: #333;
            }
            
            .btn-secondary:hover {
                background: #e0e0e0;
            }
            
            @media (max-width: 480px) {
                .driver-reminder-popup {
                    top: 10px;
                    right: 10px;
                    left: 10px;
                }
                
                .popup-container {
                    max-width: none;
                }
            }
        `;
        document.head.appendChild(style);
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.driverReminderPopup = new DriverReminderPopup();
    });
} else {
    window.driverReminderPopup = new DriverReminderPopup();
}
