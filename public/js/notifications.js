class NotificationSystem {
    constructor() {
      this.container = null
      this.init()
    }
  
    init() {
      // Create notification container if it doesn't exist
      if (!document.getElementById("notification-container")) {
        this.container = document.createElement("div")
        this.container.id = "notification-container"
        this.container.className = "notification-container"
        document.body.appendChild(this.container)
      } else {
        this.container = document.getElementById("notification-container")
      }
    }
  
    show(message, type = "info", duration = 5000) {
      const notification = document.createElement("div")
      notification.className = `notification notification-${type}`
  
      const icon = this.getIcon(type)
  
      notification.innerHTML = `
        <div class="notification-content">
          <div class="notification-icon">${icon}</div>
          <div class="notification-message">${message}</div>
          <button class="notification-close" onclick="this.parentElement.parentElement.remove()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>
      `
  
      this.container.appendChild(notification)
  
      // Animate in
      setTimeout(() => {
        notification.classList.add("notification-show")
      }, 10)
  
      // Auto remove
      if (duration > 0) {
        setTimeout(() => {
          this.remove(notification)
        }, duration)
      }
  
      return notification
    }
  
    remove(notification) {
      notification.classList.add("notification-hide")
      setTimeout(() => {
        if (notification.parentNode) {
          notification.parentNode.removeChild(notification)
        }
      }, 300)
    }
  
    getIcon(type) {
      const icons = {
        success: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="20,6 9,17 4,12"></polyline>
        </svg>`,
        error: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="15" y1="9" x2="9" y2="15"></line>
          <line x1="9" y1="9" x2="15" y2="15"></line>
        </svg>`,
        warning: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
          <line x1="12" y1="9" x2="12" y2="13"></line>
          <line x1="12" y1="17" x2="12.01" y2="17"></line>
        </svg>`,
        info: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="16" x2="12" y2="12"></line>
          <line x1="12" y1="8" x2="12.01" y2="8"></line>
        </svg>`,
      }
      return icons[type] || icons.info
    }
  
    success(message, duration = 5000) {
      return this.show(message, "success", duration)
    }
  
    error(message, duration = 5000) {
      return this.show(message, "error", duration)
    }
  
    warning(message, duration = 5000) {
      return this.show(message, "warning", duration)
    }
  
    info(message, duration = 5000) {
      return this.show(message, "info", duration)
    }
  }
  
  // Global notification instance
  window.notifications = new NotificationSystem()
  
  // Convenience functions
  window.showNotification = (message, type, duration) => {
    return window.notifications.show(message, type, duration)
  }
  
  window.showSuccess = (message, duration) => {
    return window.notifications.success(message, duration)
  }
  
  window.showError = (message, duration) => {
    return window.notifications.error(message, duration)
  }
  
  window.showWarning = (message, duration) => {
    return window.notifications.warning(message, duration)
  }
  
  window.showInfo = (message, duration) => {
    return window.notifications.info(message, duration)
  }
  