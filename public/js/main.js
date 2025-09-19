document.addEventListener("DOMContentLoaded", () => {
  // Show notifications
  function showNotification(message, type = "info") {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll(".notification")
    existingNotifications.forEach((notification) => notification.remove())

    // Create notification element
    const notification = document.createElement("div")
    notification.className = `notification notification-${type}`
    notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-message">${message}</span>
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">×</button>
            </div>
        `

    // Add to page
    document.body.appendChild(notification)

    // Auto remove after 5 seconds
    setTimeout(() => {
      if (notification.parentElement) {
        notification.remove()
      }
    }, 5000)
  }

  // Show success notification
  window.showSuccess = (message) => {
    showNotification(message, "success")
  }

  // Show error notification
  window.showError = (message) => {
    showNotification(message, "error")
  }

  // Show info notification
  window.showInfo = (message) => {
    showNotification(message, "info")
  }

  // Show warning notification
  window.showWarning = (message) => {
    showNotification(message, "warning")
  }

  // Check for flash messages from PHP
  const urlParams = new URLSearchParams(window.location.search)
  if (urlParams.has("success")) {
    window.showSuccess(decodeURIComponent(urlParams.get("success")))
  }
  if (urlParams.has("error")) {
    window.showError(decodeURIComponent(urlParams.get("error")))
  }
  if (urlParams.has("info")) {
    window.showInfo(decodeURIComponent(urlParams.get("info")))
  }

  // Profile image preview
  window.previewImage = (input) => {
    if (input.files && input.files[0]) {
      const reader = new FileReader()
      reader.onload = (e) => {
        const preview = document.getElementById("avatar-preview")
        if (preview) {
          preview.src = e.target.result
        }
      }
      reader.readAsDataURL(input.files[0])
    }
  }

  // Form validation
  window.validateProfileForm = (form) => {
    const requiredFields = form.querySelectorAll("[required]")
    let isValid = true

    requiredFields.forEach((field) => {
      if (!field.value.trim()) {
        field.classList.add("error")
        isValid = false
      } else {
        field.classList.remove("error")
      }
    })

    return isValid
  }

  // Password confirmation validation
  window.validatePasswordForm = (form) => {
    const newPassword = form.querySelector('input[name="new_password"]')
    const confirmPassword = form.querySelector('input[name="confirm_password"]')

    if (newPassword && confirmPassword) {
      if (newPassword.value !== confirmPassword.value) {
        window.showError("Mật khẩu xác nhận không khớp")
        return false
      }
      if (newPassword.value.length < 6) {
        window.showError("Mật khẩu phải có ít nhất 6 ký tự")
        return false
      }
    }

    return window.validateProfileForm(form)
  }
})
