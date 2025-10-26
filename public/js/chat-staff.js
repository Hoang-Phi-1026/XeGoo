// Real-time chat dashboard for staff
class StaffChatDashboard {
  constructor() {
    this.staffId = null
    this.currentSessionId = null
    this.sessionsList = document.getElementById("sessions-list")
    this.chatContainer = document.getElementById("chat-container")
    this.messageContainer = document.getElementById("messages-container")
    this.messageInput = document.getElementById("message-input")
    this.sendBtn = document.getElementById("send-btn")
    this.pendingCountEl = document.getElementById("pending-count")
    this.pollInterval = 2000 // Poll every 2 seconds
    this.pollTimer = null

    this.init()
  }

  init() {
    this.staffId = document.body.dataset.staffId

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

    // Start polling for sessions
    this.startPolling()
  }

  startPolling() {
    // Load sessions immediately
    this.loadSessions()
    this.updatePendingCount()

    // Then poll every 2 seconds
    this.pollTimer = setInterval(() => {
      this.loadSessions()
      this.updatePendingCount()

      // If a session is selected, also load its messages
      if (this.currentSessionId) {
        this.loadMessages()
      }
    }, this.pollInterval)
  }

  stopPolling() {
    if (this.pollTimer) {
      clearInterval(this.pollTimer)
      this.pollTimer = null
    }
  }

  loadSessions() {
    fetch("/api/chat/sessions")
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          this.displaySessions(data.sessions)
        }
      })
      .catch((error) => console.error("Error loading sessions:", error))
  }

  displaySessions(sessions) {
    if (!this.sessionsList) return

    // Clear list
    this.sessionsList.innerHTML = ""

    if (sessions.length === 0) {
      this.sessionsList.innerHTML = '<div class="empty-state">Không có cuộc trò chuyện nào</div>'
      return
    }

    sessions.forEach((session) => {
      const sessionEl = this.createSessionElement(session)
      this.sessionsList.appendChild(sessionEl)
    })
  }

  createSessionElement(session) {
    const div = document.createElement("div")
    const isActive = session.maPhien === this.currentSessionId || session.id === this.currentSessionId

    div.className = `session-item ${isActive ? "active" : ""} ${session.unreadCount > 0 ? "unread" : ""}`

    const roleLabel = session.vaiTro === "Khách hàng" ? "Khách hàng" : "Tài xế"
    const unreadBadge = session.unreadCount > 0 ? `<span class="unread-badge">${session.unreadCount}</span>` : ""

    div.innerHTML = `
            <div class="session-header">
                <div class="session-info">
                    <div class="session-name">${this.escapeHtml(session.tenNguoiDung || session.user_name || "Ẩn danh")}</div>
                    <div class="session-role">${roleLabel}</div>
                </div>
                ${unreadBadge}
            </div>
            <div class="session-preview">${this.escapeHtml(session.lastMessage || session.last_message || "Chưa có tin nhắn")}</div>
            <div class="session-time">${this.formatTime(session.ngayCapNhat || session.updated_at)}</div>
        `

    div.addEventListener("click", () => this.selectSession(session.maPhien || session.id))

    return div
  }

  selectSession(sessionId) {
    this.currentSessionId = sessionId

    // Update UI
    document.querySelectorAll(".session-item").forEach((el) => {
      el.classList.remove("active")
    })

    event.currentTarget.classList.add("active")

    // Load messages
    this.loadMessages()

    // Show chat container
    if (this.chatContainer) {
      this.chatContainer.style.display = "block"
    }

    // Focus input
    if (this.messageInput) {
      this.messageInput.focus()
    }
  }

  loadMessages() {
    if (!this.currentSessionId) return

    fetch(`/api/chat/messages/${this.currentSessionId}`)
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
      this.messageContainer.innerHTML = '<div class="empty-state">Chưa có tin nhắn nào</div>'
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
    const isStaff = msg.vaiTroNguoiGui === "Nhân viên" || msg.sender_role === "staff"

    div.className = `message ${isStaff ? "own" : "other"}`
    div.innerHTML = `
            <div class="message-content">
                <div class="message-sender">${this.escapeHtml(msg.tenNhanVien || msg.tenNguoiDung || msg.sender_name || "Ẩn danh")}</div>
                <div class="message-text">${this.escapeHtml(msg.noiDung || msg.message)}</div>
                <div class="message-time">${this.formatTime(msg.ngayTao || msg.created_at)}</div>
            </div>
        `

    return div
  }

  sendMessage() {
    const message = this.messageInput.value.trim()

    if (!message || !this.currentSessionId) {
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
        session_id: this.currentSessionId,
        message: message,
        sender_role: "staff",
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

  markAsRead() {
    if (!this.currentSessionId) return

    fetch("/api/chat/mark-read", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        session_id: this.currentSessionId,
      }),
    }).catch((error) => console.error("Error marking as read:", error))
  }

  updatePendingCount() {
    fetch("/api/chat/pending-count")
      .then((response) => response.json())
      .then((data) => {
        if (data.success && this.pendingCountEl) {
          this.pendingCountEl.textContent = data.count

          // Show/hide badge
          if (data.count > 0) {
            this.pendingCountEl.style.display = "inline-block"
          } else {
            this.pendingCountEl.style.display = "none"
          }
        }
      })
      .catch((error) => console.error("Error updating pending count:", error))
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
  window.staffChatDashboard = new StaffChatDashboard()
})
