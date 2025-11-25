/**
 * Popup Notification System - Hiển thị thông báo popup về delay và hủy chuyến
 * Tương tự như DriverReminderPopup nhưng cho khách hàng
 */

class PopupNotificationSystem {
  constructor(options = {}) {
    this.baseUrl = window.BASE_URL || ""
    this.pollInterval = options.pollInterval || 30000 // Poll every 30 seconds
    this.displayedNotifications = new Set()
    this.notificationQueue = []
    this.currentNotification = null
    this.autoCloseDelay = options.autoCloseDelay || 8000 // 8 seconds
    this.maxVisibleNotifications = options.maxVisibleNotifications || 3
    this.position = options.position || "right" // 'right' or 'left'
    this.pollTimer = null
    this.init()
  }

  init() {
    console.log("[PopupNotificationSystem] Initializing popup notification system")

    // Ensure base URL is set
    if (!this.baseUrl || this.baseUrl === "") {
      const pathname = window.location.pathname
      const baseMatch = pathname.match(/^(.*?)(?:\/[^/]*)?$/)
      if (baseMatch && pathname.includes("/xegoo")) {
        this.baseUrl = pathname.substring(0, pathname.indexOf("/xegoo") + 6)
      } else {
        this.baseUrl = window.location.origin
      }
    }

    // Create container
    this.createContainer()

    // Check if user is logged in
    if (this.isUserLoggedIn()) {
      this.startPolling()
    } else {
      console.log("[PopupNotificationSystem] User not logged in, skipping polling")
    }
  }

  isUserLoggedIn() {
    const userElement = document.querySelector("[data-user-id]")
    if (userElement) return true

    if (document.body.dataset.userId) return true

    const userMenu = document.querySelector(".user-menu .user-info")
    return !!userMenu
  }

  createContainer() {
    if (document.getElementById("popup-notification-container")) {
      return
    }

    const container = document.createElement("div")
    container.id = "popup-notification-container"
    container.className = `popup-notification-container ${this.position}`
    document.body.appendChild(container)

    console.log("[PopupNotificationSystem] Container created")
  }

  startPolling() {
    this.checkNotifications()

    this.pollTimer = setInterval(() => {
      this.checkNotifications()
    }, this.pollInterval)

    console.log("[PopupNotificationSystem] Started polling for notifications")
  }

  stopPolling() {
    if (this.pollTimer) {
      clearInterval(this.pollTimer)
      console.log("[PopupNotificationSystem] Stopped polling")
    }
  }

  checkNotifications() {
    const url = `${this.baseUrl}/api/popup-notifications/pending`

    fetch(url, {
      method: "GET",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
      },
      credentials: "same-origin",
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`)
        }
        return response.json()
      })
      .then((data) => {
        if (data.success && data.notifications && data.notifications.length > 0) {
          console.log("[PopupNotificationSystem] Found " + data.notifications.length + " pending notifications")
          data.notifications.forEach((notification) => {
            if (!this.displayedNotifications.has(notification.maPopupNotification)) {
              this.addToQueue(notification)
              this.displayedNotifications.add(notification.maPopupNotification)
            }
          })
          this.processQueue()
        }
      })
      .catch((error) => {
        console.error("[PopupNotificationSystem] Error checking notifications:", error)
      })
  }

  addToQueue(notification) {
    this.notificationQueue.push(notification)
    console.log("[PopupNotificationSystem] Added notification to queue, total: " + this.notificationQueue.length)
  }

  processQueue() {
    const container = document.getElementById("popup-notification-container")
    const visibleCount = container.querySelectorAll(".popup-notification:not(.closing)").length

    if (visibleCount < this.maxVisibleNotifications && this.notificationQueue.length > 0) {
      const notification = this.notificationQueue.shift()
      this.displayNotification(notification)

      // Process next after this one closes
      setTimeout(() => this.processQueue(), this.autoCloseDelay + 500)
    }
  }

  displayNotification(notification) {
    const container = document.getElementById("popup-notification-container")
    const notificationType = notification.loaiThongBao || "delay-popup"

    const notificationEl = document.createElement("div")
    notificationEl.className = `popup-notification ${notificationType.replace("-popup", "")}`
    notificationEl.id = `popup-notification-${notification.maPopupNotification}`

    // Determine icon and styling based on notification type
    let icon = "fas fa-clock"
    let bgColor = "delay"

    if (notificationType.includes("cancellation")) {
      icon = "fas fa-times-circle"
      bgColor = "cancellation"
    } else if (notificationType.includes("delay")) {
      icon = "fas fa-hourglass-half"
      bgColor = "delay"
    }

    // Format time
    const departureTime = this.formatDepartureTime(notification.ngayKhoiHanh, notification.thoiGianKhoiHanh)

    // Build trip info section
    const tripInfoHtml = `
            <div class="popup-trip-info ${bgColor}">
                <div class="popup-trip-info-row">
                    <i class="fas fa-bus"></i>
                    <span class="popup-trip-label">Tuyến:</span>
                    <span class="popup-trip-value">${this.escapeHtml(notification.kyHieuTuyen)}</span>
                </div>
                <div class="popup-trip-info-row">
                    <i class="fas fa-map-marker-alt"></i>
                    <span class="popup-trip-label">Hành trình:</span>
                    <span class="popup-trip-value">${this.escapeHtml(notification.diemDi)} → ${this.escapeHtml(notification.diemDen)}</span>
                </div>
                <div class="popup-trip-info-row">
                    <i class="fas fa-clock"></i>
                    <span class="popup-trip-label">Khởi hành:</span>
                    <span class="popup-trip-value">${departureTime}</span>
                </div>
                ${
                  notification.soLuongVe
                    ? `
                <div class="popup-trip-info-row">
                    <i class="fas fa-ticket-alt"></i>
                    <span class="popup-trip-label">Vé của bạn:</span>
                    <span class="popup-trip-value">${notification.soLuongVe} vé</span>
                </div>
                `
                    : ""
                }
            </div>
        `

    notificationEl.innerHTML = `
            <div class="popup-notification-header">
                <div class="popup-notification-header-text">
                    <div class="popup-notification-icon">
                        <i class="${icon}"></i>
                    </div>
                </div>
                <h3>${this.escapeHtml(notification.tieu_de)}</h3>
                <button class="popup-notification-close" type="button" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="popup-notification-body">
                <p class="popup-notification-message">${this.escapeHtml(notification.noi_dung)}</p>
                ${tripInfoHtml}
            </div>
            <div class="popup-notification-footer">
                <a href="${this.baseUrl}/my-tickets/detail/${notification.maDatVe}" 
                   class="popup-notification-btn popup-notification-btn-primary">
                    <i class="fas fa-eye"></i> Xem vé
                </a>
                <button class="popup-notification-btn popup-notification-btn-secondary close-btn" type="button">
                    <i class="fas fa-check"></i> Đã hiểu
                </button>
            </div>
            <div class="popup-notification-timer"></div>
        `

    container.appendChild(notificationEl)

    // Attach event listeners
    const closeBtn = notificationEl.querySelector(".popup-notification-close")
    const submitBtn = notificationEl.querySelector(".close-btn")

    if (closeBtn) {
      closeBtn.addEventListener("click", () => {
        this.closeNotification(notification.maPopupNotification, notificationEl)
      })
    }

    if (submitBtn) {
      submitBtn.addEventListener("click", () => {
        this.closeNotification(notification.maPopupNotification, notificationEl)
      })
    }

    // Auto-close after delay
    setTimeout(() => {
      if (notificationEl.parentElement) {
        this.closeNotification(notification.maPopupNotification, notificationEl, true)
      }
    }, this.autoCloseDelay)

    console.log("[PopupNotificationSystem] Notification displayed:", notification.maPopupNotification)

    // Update notification status to "shown"
    this.updateNotificationStatus(notification.maPopupNotification, "shown")
  }

  closeNotification(maPopupNotification, notificationEl, isAutoClose = false) {
    if (!notificationEl || !notificationEl.parentElement) {
      return
    }

    // Add closing animation
    notificationEl.classList.add("closing")

    // Remove after animation
    setTimeout(() => {
      if (notificationEl.parentElement) {
        notificationEl.remove()
      }
      this.processQueue()
    }, 300)

    // Update status on server
    if (isAutoClose) {
      this.updateNotificationStatus(maPopupNotification, "shown")
    } else {
      this.updateNotificationStatus(maPopupNotification, "dismissed")
    }

    console.log("[PopupNotificationSystem] Notification closed:", maPopupNotification)
  }

  updateNotificationStatus(maPopupNotification, trangThai) {
    const url = `${this.baseUrl}/api/popup-notifications/update-status`

    fetch(url, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        maPopupNotification: maPopupNotification,
        trangThai: trangThai,
      }),
      credentials: "same-origin",
    }).catch((error) => {
      console.warn("[PopupNotificationSystem] Error updating status:", error)
    })
  }

  escapeHtml(text) {
    if (!text) return ""
    const map = {
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#039;",
    }
    return text.replace(/[&<>"']/g, (char) => map[char])
  }

  formatDepartureTime(ngayKhoiHanh, thoiGianKhoiHanh) {
    try {
      if (!ngayKhoiHanh || !thoiGianKhoiHanh) {
        return "N/A"
      }

      let dateTimeString = ngayKhoiHanh

      if (thoiGianKhoiHanh && thoiGianKhoiHanh.length <= 8) {
        dateTimeString = ngayKhoiHanh + " " + thoiGianKhoiHanh
      } else if (thoiGianKhoiHanh) {
        const timePart = thoiGianKhoiHanh.substring(11, 19) || thoiGianKhoiHanh.substring(0, 8)
        dateTimeString = ngayKhoiHanh + " " + timePart
      }

      const date = new Date(dateTimeString)

      if (isNaN(date.getTime())) {
        return ngayKhoiHanh + " " + (thoiGianKhoiHanh || "")
      }

      const options = {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
      }

      return date.toLocaleDateString("vi-VN", options)
    } catch (error) {
      console.warn("[PopupNotificationSystem] Error formatting date:", error)
      return ngayKhoiHanh + " " + (thoiGianKhoiHanh || "")
    }
  }
}

// Initialize when DOM is ready
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    window.popupNotificationSystem = new PopupNotificationSystem({
      position: "right",
      pollInterval: 30000,
      autoCloseDelay: 8000,
      maxVisibleNotifications: 3,
    })
  })
} else {
  window.popupNotificationSystem = new PopupNotificationSystem({
    position: "right",
    pollInterval: 30000,
    autoCloseDelay: 8000,
    maxVisibleNotifications: 3,
  })
}
