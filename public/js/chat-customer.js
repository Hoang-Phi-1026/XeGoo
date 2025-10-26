// Real-time chat for customers and drivers
class CustomerChat {
  constructor() {
    this.sessionId = null
    this.userId = null
    this.userRole = null
    this.messageContainer = document.getElementById("messages-container")
    this.messageInput = document.getElementById("message-input")
    this.sendBtn = document.getElementById("send-btn")
    this.pollInterval = 3000 // Poll every 3 seconds
    this.pollTimer = null

    this.init()
  }

  init() {
    // Get user info from page data attributes
    this.userId = document.body.dataset.userId
    this.userRole = document.body.dataset.userRole // 'customer' or 'driver'
    this.sessionId = document.body.dataset.sessionId

    // Bind events
    if (this.sendBtn) {
      this.sendBtn.addEventListener("click", () => this.sendMessage())
    }

    if (this.messageInput) {
      this.messageInput.addEventListener("keypress", (e) => {
        if (e.key === "Enter" && !e.shiftKey) {
          e.preventDefault()
          this.sendMessage()
        }
      })
    }

    // Create session if not exists
    if (!this.sessionId) {
      this.createSession()
    } else {
      this.startPolling()
    }
  }

  createSession() {
    fetch("/api/chat/create-session", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        user_id: this.userId,
        user_role: this.userRole,
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          this.sessionId = data.session_id
          document.body.dataset.sessionId = this.sessionId
          this.startPolling()
          this.loadMessages()
        } else {
          console.error("Failed to create chat session:", data.message)
          this.showError("Không thể tạo phiên chat. Vui lòng thử lại.")
        }
      })
      .catch((error) => {
        console.error("Error creating session:", error)
        this.showError("Lỗi kết nối. Vui lòng thử lại.")
      })
  }

  sendMessage() {
    const message = this.messageInput.value.trim()

    if (!message) {
      return
    }

    if (!this.sessionId) {
      this.showError("Phiên chat chưa được tạo. Vui lòng thử lại.")
      return
    }

    this.messageInput.value = ""

    // Disable send button
    this.sendBtn.disabled = true

    fetch("/api/chat/send", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        session_id: this.sessionId,
        message: message,
        sender_role: this.userRole,
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          this.loadMessages()
        } else {
          this.showError("Không thể gửi tin nhắn. Vui lòng thử lại.")
        }
      })
      .catch((error) => {
        console.error("Error sending message:", error)
        this.showError("Lỗi kết nối. Vui lòng thử lại.")
      })
      .finally(() => {
        this.sendBtn.disabled = false
        this.messageInput.focus()
      })
  }

  loadMessages() {
    if (!this.sessionId) return

    fetch(`/api/chat/messages/${this.sessionId}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          this.displayMessages(data.messages)
          // Mark messages as read
          this.markAsRead()
        }
      })
      .catch((error) => console.error("Error loading messages:", error))
  }

  displayMessages(messages) {
    if (!this.messageContainer) return

    // Clear container
    this.messageContainer.innerHTML = ""

    if (messages.length === 0) {
      this.messageContainer.innerHTML =
        '<div class="empty-state">Chưa có tin nhắn nào. Hãy bắt đầu cuộc trò chuyện!</div>'
      return
    }

    messages.forEach((msg) => {
      const messageEl = this.createMessageElement(msg)
      this.messageContainer.appendChild(messageEl)
    })

    // Scroll to bottom
    this.messageContainer.scrollTop = this.messageContainer.scrollHeight
  }

  createMessageElement(msg) {
    const div = document.createElement("div")
    const isOwn = msg.vaiTroNguoiGui === this.userRole || msg.sender_role === this.userRole

    div.className = `message ${isOwn ? "own" : "other"}`
    div.innerHTML = `
            <div class="message-content">
                <div class="message-text">${this.escapeHtml(msg.noiDung || msg.message)}</div>
                <div class="message-time">${this.formatTime(msg.ngayTao || msg.created_at)}</div>
            </div>
        `

    return div
  }

  startPolling() {
    // Load messages immediately
    this.loadMessages()

    // Then poll every 3 seconds
    this.pollTimer = setInterval(() => {
      this.loadMessages()
    }, this.pollInterval)
  }

  stopPolling() {
    if (this.pollTimer) {
      clearInterval(this.pollTimer)
      this.pollTimer = null
    }
  }

  markAsRead() {
    if (!this.sessionId) return

    fetch("/api/chat/mark-read", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        session_id: this.sessionId,
      }),
    }).catch((error) => console.error("Error marking as read:", error))
  }

  formatTime(dateString) {
    const date = new Date(dateString)
    const hours = String(date.getHours()).padStart(2, "0")
    const minutes = String(date.getMinutes()).padStart(2, "0")
    return `${hours}:${minutes}`
  }

  escapeHtml(text) {
    const div = document.createElement("div")
    div.textContent = text
    return div.innerHTML
  }

  showError(message) {
    const errorEl = document.createElement("div")
    errorEl.className = "error-message"
    errorEl.textContent = message

    if (this.messageContainer) {
      this.messageContainer.appendChild(errorEl)
    }

    setTimeout(() => {
      errorEl.remove()
    }, 5000)
  }

  destroy() {
    this.stopPolling()
  }
}

// Initialize when DOM is ready
document.addEventListener("DOMContentLoaded", () => {
  window.customerChat = new CustomerChat()
})
