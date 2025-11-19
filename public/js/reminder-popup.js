/**
 * ReminderPopup - Hiển thị thông báo popup nhắc khách hàng
 * Chạy khi trang chủ load nếu có thông báo chưa đọc
 */
class ReminderPopup {
    constructor() {
        this.notifications = [];
        this.currentNotificationIndex = 0;
        this.baseUrl = window.BASE_URL || '';
    }

    /**
     * Khởi tạo - lấy thông báo từ server
     */
    async init() {
        if (!this.isUserLoggedIn()) {
            console.log('[ReminderPopup] User not logged in, skipping initialization');
            return;
        }

        try {
            const response = await fetch(`${this.baseUrl}/api/notifications/unread`);
            
            if (!response.ok) {
                console.error('[ReminderPopup] Failed to fetch notifications:', response.status);
                return;
            }

            const data = await response.json();

            if (data.success && data.notifications && data.notifications.length > 0) {
                this.notifications = data.notifications;
                console.log('[ReminderPopup] Loaded', this.notifications.length, 'unread notifications');
                this.showNextNotification();
            }
        } catch (error) {
            console.error('[ReminderPopup] Error loading notifications:', error);
        }
    }

    /**
     * Kiểm tra xem người dùng đã đăng nhập chưa
     */
    isUserLoggedIn() {
        const userElement = document.querySelector('[data-user-id]');
        if (userElement) {
            return true;
        }
        
        // Fallback: check if body has user-id dataset
        if (document.body.dataset.userId) {
            return true;
        }

        // Fallback: check if there's any indication of logged-in state
        const userMenu = document.querySelector('.user-menu .user-info');
        return !!userMenu;
    }

    /**
     * Hiển thị thông báo tiếp theo
     */
    showNextNotification() {
        if (this.currentNotificationIndex >= this.notifications.length) {
            console.log('[ReminderPopup] All notifications displayed');
            return;
        }

        const notification = this.notifications[this.currentNotificationIndex];
        this.showPopup(notification);
    }

    /**
     * Hiển thị popup thông báo
     */
    showPopup(notification) {
        if (!notification || !notification.maThongBao) {
            console.error('[ReminderPopup] Invalid notification data:', notification);
            return;
        }

        if (!notification.kyHieuTuyen || !notification.thoiGianKhoiHanh_trip) {
            console.warn('[ReminderPopup] Missing required notification fields');
            console.warn('[ReminderPopup] Notification data:', notification);
            this.currentNotificationIndex++;
            this.showNextNotification();
            return;
        }

        // Tạo overlay (nền tối)
        const overlay = document.createElement('div');
        overlay.className = 'reminder-popup-overlay';
        overlay.id = 'reminder-overlay-' + notification.maThongBao;

        // Tạo modal popup
        const modal = document.createElement('div');
        modal.className = 'reminder-popup-modal';
        modal.id = 'reminder-modal-' + notification.maThongBao;

        const departureTime = this.formatDepartureTime(
            notification.ngayKhoiHanh,
            notification.thoiGianKhoiHanh_trip
        );

        // Escape HTML to prevent XSS
        const escapedTitle = this.escapeHtml(notification.tieu_de);
        const escapedRoute = this.escapeHtml(notification.kyHieuTuyen);
        const escapedFromLocation = this.escapeHtml(notification.diemDi);
        const escapedToLocation = this.escapeHtml(notification.diemDen);
        const escapedContent = this.escapeHtml(notification.noi_dung);

        // Nội dung modal
        modal.innerHTML = `
            <div class="reminder-popup-content">
                <div class="reminder-popup-header">
                    <h2 class="reminder-popup-title">
                        <i class="fas fa-bell"></i>
                        ${escapedTitle}
                    </h2>
                    <button class="reminder-popup-close" data-notification-id="${notification.maThongBao}" type="button" aria-label="Close notification">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="reminder-popup-body">
                    <div class="reminder-trip-info">
                        <div class="reminder-trip-route">
                            <div class="reminder-stop">
                                <span class="reminder-icon from">
                                    <i class="fas fa-map-marker-alt"></i>
                                </span>
                                <span class="reminder-location">${escapedFromLocation}</span>
                            </div>
                            <div class="reminder-arrow">
                                <i class="fas fa-arrow-right"></i>
                            </div>
                            <div class="reminder-stop">
                                <span class="reminder-icon to">
                                    <i class="fas fa-map-marker-alt"></i>
                                </span>
                                <span class="reminder-location">${escapedToLocation}</span>
                            </div>
                        </div>

                        <div class="reminder-trip-details">
                            <div class="reminder-detail-row">
                                <span class="reminder-label">
                                    <i class="fas fa-bus"></i>
                                    Tuyến:
                                </span>
                                <span class="reminder-value">${escapedRoute}</span>
                            </div>
                            <div class="reminder-detail-row">
                                <span class="reminder-label">
                                    <i class="fas fa-clock"></i>
                                    Khởi hành:
                                </span>
                                <span class="reminder-value">${departureTime}</span>
                            </div>
                            <div class="reminder-detail-row">
                                <span class="reminder-label">
                                    <i class="fas fa-ticket-alt"></i>
                                    Số vé:
                                </span>
                                <span class="reminder-value">${notification.soLuongVe} vé</span>
                            </div>
                        </div>

                        <div class="reminder-message">
                            ${escapedContent}
                        </div>
                    </div>
                </div>

                <div class="reminder-popup-footer">
                    <a href="${this.baseUrl}/my-tickets/detail/${notification.maDatVe}" 
                       class="reminder-btn reminder-btn-primary reminder-view-ticket"
                       data-notification-id="${notification.maThongBao}">
                        <i class="fas fa-eye"></i>
                        Xem chi tiết vé
                    </a>
                    <button class="reminder-btn reminder-btn-secondary reminder-close-btn"
                            data-notification-id="${notification.maThongBao}"
                            type="button">
                        <i class="fas fa-check"></i>
                        Đã hiểu
                    </button>
                </div>
            </div>
        `;

        // Thêm vào body
        overlay.appendChild(modal);
        document.body.appendChild(overlay);

        this.attachEventListeners(notification.maThongBao, overlay);

        // Animate in
        setTimeout(() => {
            overlay.classList.add('reminder-popup-show');
            modal.classList.add('reminder-popup-show');
        }, 10);
    }

    /**
     * Attach event listeners to close buttons
     */
    attachEventListeners(maThongBao, overlay) {
        const closeBtn = overlay.querySelector('.reminder-popup-close');
        const confirmBtn = overlay.querySelector('.reminder-close-btn');
        const viewTicketLink = overlay.querySelector('.reminder-view-ticket');

        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.closeNotification(maThongBao));
        }

        if (confirmBtn) {
            confirmBtn.addEventListener('click', () => this.closeNotification(maThongBao));
        }

        if (viewTicketLink) {
            viewTicketLink.addEventListener('click', () => this.closeNotification(maThongBao));
        }
    }

    /**
     * Đóng thông báo và hiển thị thông báo tiếp theo
     */
    async closeNotification(maThongBao) {
        const overlay = document.getElementById('reminder-overlay-' + maThongBao);
        const modal = document.getElementById('reminder-modal-' + maThongBao);

        if (overlay && modal) {
            // Animate out
            overlay.classList.remove('reminder-popup-show');
            modal.classList.remove('reminder-popup-show');

            // Xóa element sau animation
            setTimeout(() => {
                if (overlay.parentNode) {
                    overlay.parentNode.removeChild(overlay);
                }
            }, 300);
        }

        // Đánh dấu thông báo là đã đọc trên server
        try {
            const response = await fetch(`${this.baseUrl}/api/notifications/mark-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ maThongBao: maThongBao })
            });

            if (!response.ok) {
                console.error('[ReminderPopup] Failed to mark notification as read:', response.status);
            }
        } catch (error) {
            console.error('[ReminderPopup] Error marking notification as read:', error);
        }

        // Hiển thị thông báo tiếp theo
        this.currentNotificationIndex++;
        setTimeout(() => {
            this.showNextNotification();
        }, 300);
    }

    /**
     * Escape HTML entities to prevent XSS attacks
     */
    escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, char => map[char]);
    }

    /**
     * Format thời gian khởi hành
     */
    formatDepartureTime(ngayKhoiHanh, thoiGianKhoiHanh) {
        try {
            if (!ngayKhoiHanh || !thoiGianKhoiHanh) {
                console.warn('[ReminderPopup] Missing date/time data:', { ngayKhoiHanh, thoiGianKhoiHanh });
                return 'N/A';
            }

            let dateTimeString = ngayKhoiHanh;
            
            // If thoiGianKhoiHanh is only time (HH:mm:ss format)
            if (thoiGianKhoiHanh && thoiGianKhoiHanh.length <= 8) {
                dateTimeString = ngayKhoiHanh + ' ' + thoiGianKhoiHanh;
            } else if (thoiGianKhoiHanh) {
                // If it's already full datetime, extract just the time part
                const timePart = thoiGianKhoiHanh.substring(11, 19) || thoiGianKhoiHanh.substring(0, 8);
                dateTimeString = ngayKhoiHanh + ' ' + timePart;
            }
            
            const date = new Date(dateTimeString);
            
            if (isNaN(date.getTime())) {
                console.warn('[ReminderPopup] Invalid date format:', dateTimeString);
                return ngayKhoiHanh + ' ' + (thoiGianKhoiHanh || '');
            }

            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            return date.toLocaleDateString('vi-VN', options);
        } catch (error) {
            console.warn('[ReminderPopup] Error formatting date:', error);
            return ngayKhoiHanh + ' ' + (thoiGianKhoiHanh || '');
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const reminderPopupManager = new ReminderPopup();
    reminderPopupManager.init();
    
    window.reminderPopupManager = reminderPopupManager;
});
