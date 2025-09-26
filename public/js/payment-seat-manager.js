class PaymentSeatManager {
  constructor() {
    this.isPageActive = true
    this.heartbeatInterval = null
    this.init()
  }

  init() {
    // Handle page visibility changes
    document.addEventListener("visibilitychange", () => {
      if (document.hidden) {
        this.handlePageHidden()
      } else {
        this.handlePageVisible()
      }
    })

    // Handle beforeunload (back button, close tab, etc.)
    window.addEventListener("beforeunload", (e) => {
      this.releaseSeats()
    })

    // Handle page unload
    window.addEventListener("unload", () => {
      this.releaseSeats()
    })

    // Start heartbeat to keep seats held
    this.startHeartbeat()

    // Auto-release after 10 minutes
    setTimeout(
      () => {
        this.releaseSeats()
        window.location.href = "/booking"
      },
      10 * 60 * 1000,
    ) // 10 minutes
  }

  handlePageHidden() {
    this.isPageActive = false
    // Give user 30 seconds to come back before releasing seats
    setTimeout(() => {
      if (!this.isPageActive) {
        this.releaseSeats()
      }
    }, 30000)
  }

  handlePageVisible() {
    this.isPageActive = true
  }

  startHeartbeat() {
    // Send heartbeat every 2 minutes to keep seats held
    this.heartbeatInterval = setInterval(
      () => {
        if (this.isPageActive) {
          this.sendHeartbeat()
        }
      },
      2 * 60 * 1000,
    )
  }

  sendHeartbeat() {
    fetch("/payment/heartbeat", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ action: "keep_alive" }),
    }).catch((error) => {
      console.error("Heartbeat failed:", error)
    })
  }

  releaseSeats() {
    // Use sendBeacon for reliable delivery even when page is closing
    if (navigator.sendBeacon) {
      navigator.sendBeacon("/payment/release-seats", JSON.stringify({ action: "release" }))
    } else {
      // Fallback for older browsers
      fetch("/payment/release-seats", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ action: "release" }),
        keepalive: true,
      }).catch((error) => {
        console.error("Failed to release seats:", error)
      })
    }
  }

  destroy() {
    if (this.heartbeatInterval) {
      clearInterval(this.heartbeatInterval)
    }
  }
}

// Initialize seat manager when page loads
document.addEventListener("DOMContentLoaded", () => {
  if (window.location.pathname.includes("/payment")) {
    window.seatManager = new PaymentSeatManager()
  }
})
