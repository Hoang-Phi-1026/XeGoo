/**
 * Driver Reminder Popup System
 * Polls for unread notifications and displays popups for trip reminders
 */

class DriverReminderPopup {
  constructor() {
    this.pollInterval = 15000 // Poll every 15 seconds
    this.displayedNotifications = new Set()
    this.baseUrl = window.BASE_URL || ""
    this.init()
  }

  init() {
    console.log("[v0] Initializing driver reminder popup system")
    if (!window.BASE_URL || window.BASE_URL === "") {
      // Extract base URL from current path
      const pathname = window.location.pathname
      // Remove trailing slashes and extract base path
      const baseMatch = pathname.match(/^(.*?)(?:\/[^/]*)?$/)
      if (baseMatch && pathname.includes("/xegoo")) {
        this.baseUrl = pathname.substring(0, pathname.indexOf("/xegoo") + 6)
      } else {
        this.baseUrl = window.location.origin
      }
    }
    console.log("[v0] Base URL set to:", this.baseUrl)
    this.startPolling()
  }

  startPolling() {
    // Initial check
    this.checkNotifications()

    // Poll regularly - poll every 15 seconds
    this.pollTimer = setInterval(() => {
      this.checkNotifications()
    }, this.pollInterval)

    console.log("[v0] Started polling for driver notifications")
  }

  stopPolling() {
    if (this.pollTimer) {
      clearInterval(this.pollTimer)
    }
  }

  checkNotifications() {
    const url = `${this.baseUrl}/api/driver/notifications/unread`
    console.log("[v0] Polling for notifications:", url)

    fetch(url, {
      method: "GET",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
      },
      credentials: "same-origin",
    })
      .then((response) => {
        console.log("[v0] Response status:", response.status)
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`)
        }
        return response.text()
      })
      .then((text) => {
        console.log("[v0] Response text:", text.substring(0, 200))
        try {
          const data = JSON.parse(text)
          console.log("[v0] Parsed data successfully")
          if (data.success && data.notifications && data.notifications.length > 0) {
            console.log("[v0] Found " + data.notifications.length + " unread notifications")
            data.notifications.forEach((notification) => {
              if (!this.displayedNotifications.has(notification.maThongBao)) {
                console.log("[v0] Displaying notification:", notification.maThongBao)
                this.showReminderPopup(notification)
                this.displayedNotifications.add(notification.maThongBao)
              }
            })
          } else {
            console.log("[v0] No notifications found or API error:", data.message)
          }
        } catch (parseError) {
          console.error("[v0] Failed to parse JSON:", parseError, "Text:", text.substring(0, 300))
        }
      })
      .catch((error) => {
        console.error("[v0] Error checking notifications:", error)
      })
  }

  showReminderPopup(notification) {
    console.log("[v0] Showing reminder popup for trip:", notification.maChuyenXe)

    const overlay = document.createElement("div")
    overlay.className = "driver-reminder-overlay"
    document.body.appendChild(overlay)

    const popup = document.createElement("div")
    popup.className = "driver-reminder-popup"
    popup.innerHTML = `
            <div class="popup-container">
                <div class="popup-header">
                    <div class="popup-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h3>${this.escapeHtml(notification.tieu_de || "Thông báo")}</h3>
                    <button class="popup-close" onclick="this.closest('.driver-reminder-popup').classList.add('closing'); setTimeout(() => this.closest('.driver-reminder-popup').remove(), 300)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="popup-body">
                    <p class="popup-message">${this.escapeHtml(notification.noi_dung || "")}</p>
                    
                    <div class="trip-info-box">
                        <div class="info-row">
                            <i class="fas fa-clock"></i>
                            <span>Khởi hành lúc: <strong>${this.formatTime(notification.thoiGianKhoiHanh)}</strong></span>
                        </div>
                    </div>
                    
                    <div class="popup-actions">
                        <button class="btn-primary" onclick="window.location.href = '${this.baseUrl}/driver/report'">
                            <i class="fas fa-arrow-right"></i>
                            Xem chuyến xe
                        </button>
                        <button class="btn-secondarys" onclick="window.driverReminderPopup.markAsRead('${notification.maThongBao}')">
                            <i class="fas fa-check"></i>
                            Đã biết
                        </button>
                    </div>
                </div>
            </div>
        `

    document.body.appendChild(popup)
    console.log("[v0] Popup added to DOM")

    // Auto-remove after 10 seconds
    setTimeout(() => {
      if (popup.parentElement) {
        console.log("[v0] Auto-removing popup")
        popup.classList.add("closing")
        setTimeout(() => {
          popup.remove()
          overlay.remove() // Remove overlay with popup
        }, 300)
      }
    }, 10000)

    const closeBtn = popup.querySelector(".popup-close")
    if (closeBtn) {
      closeBtn.addEventListener("click", () => {
        setTimeout(() => {
          if (!popup.parentElement && overlay.parentElement) {
            overlay.remove()
          }
        }, 300)
      })
    }
  }

  markAsRead(maThongBao) {
    const url = `${this.baseUrl}/api/driver/notifications/mark-read`
    console.log("[v0] Marking notification as read:", url)

    fetch(url, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ maThongBao }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          console.log("[v0] Marked notification as read")
          document.querySelectorAll(".driver-reminder-popup").forEach((p) => {
            p.classList.add("closing")
            setTimeout(() => p.remove(), 300)
          })
          document.querySelectorAll(".driver-reminder-overlay").forEach((o) => {
            o.remove()
          })
        }
      })
      .catch((error) => console.error("[v0] Error marking as read:", error))
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

  formatTime(datetime) {
    try {
      if (!datetime) return "N/A"

      let dateObj = new Date(datetime)

      // Handle string format YYYY-MM-DD HH:mm:ss
      if (isNaN(dateObj.getTime())) {
        // Try parsing as local time
        const parts = datetime.split(/[\s\-:]/)
        if (parts.length >= 3) {
          dateObj = new Date(
            parts[0],
            Number.parseInt(parts[1]) - 1,
            parts[2],
            parts[3] || 0,
            parts[4] || 0,
            parts[5] || 0,
          )
        }
      }

      if (isNaN(dateObj.getTime())) {
        return datetime || "N/A"
      }

      return dateObj.toLocaleString("vi-VN", {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
      })
    } catch (error) {
      console.warn("[v0] Error formatting date:", error)
      return datetime || "N/A"
    }
  }
}

// Initialize when DOM is ready
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    window.driverReminderPopup = new DriverReminderPopup()
  })
} else {
  window.driverReminderPopup = new DriverReminderPopup()
}
