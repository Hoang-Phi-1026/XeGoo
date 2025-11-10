class PaymentManager {
  constructor() {
    this.holdTimer = null
    this.timeRemaining = 600 // 10 minutes in seconds
    this.init()
  }

  init() {
    this.startHoldTimer()
    this.bindEvents()
    this.loadPromotions()
    this.loadLoyaltyPoints()
  }

  bindEvents() {
    // Cancel button
    document.getElementById("cancelPayment")?.addEventListener("click", () => {
      this.cancelPayment()
    })

    // Confirm payment button
    document.getElementById("confirmPayment")?.addEventListener("click", () => {
      this.processPayment()
    })

    // Promotion selection
    document.querySelectorAll(".promotion-item").forEach((item) => {
      item.addEventListener("click", () => {
        this.selectPromotion(item)
      })
    })

    // Loyalty points input
    document.getElementById("loyaltyPointsInput")?.addEventListener("input", (e) => {
      this.calculateLoyaltyDiscount(e.target.value)
    })

    // Payment method selection
    document.querySelectorAll('input[name="payment_method"]').forEach((radio) => {
      radio.addEventListener("change", () => {
        this.updatePaymentMethod(radio.value)
      })
    })
  }

  startHoldTimer() {
    const timerElement = document.getElementById("holdTimer")
    if (!timerElement) return

    this.holdTimer = setInterval(() => {
      this.timeRemaining--

      const minutes = Math.floor(this.timeRemaining / 60)
      const seconds = this.timeRemaining % 60

      timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, "0")}`

      if (this.timeRemaining <= 0) {
        this.timeExpired()
      }
    }, 1000)
  }

  timeExpired() {
    clearInterval(this.holdTimer)
    alert("Thời gian giữ ghế đã hết! Bạn sẽ được chuyển về trang tìm kiếm.")
    this.releaseSeats()
    window.location.href = "/search"
  }

  async cancelPayment() {
    if (confirm("Bạn có chắc chắn muốn hủy thanh toán?")) {
      try {
        const response = await fetch("/payment/cancel", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
        })

        if (response.ok) {
          window.location.href = "/search"
        } else {
          alert("Có lỗi xảy ra khi hủy thanh toán")
        }
      } catch (error) {
        console.error("Cancel payment error:", error)
        alert("Có lỗi xảy ra khi hủy thanh toán")
      }
    }
  }

  async processPayment() {
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value

    if (!paymentMethod) {
      alert("Vui lòng chọn phương thức thanh toán")
      return
    }

    try {
      const response = await fetch("/payment/process", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          payment_method: paymentMethod,
          promotion_code: this.getSelectedPromotion(),
          loyalty_points: this.getUsedLoyaltyPoints(),
        }),
      })

      const result = await response.json()

      if (result.success) {
        if (result.redirect_url) {
          window.location.href = result.redirect_url
        } else {
          window.location.href = "/booking/success/" + result.booking_id
        }
      } else {
        alert(result.message || "Có lỗi xảy ra khi xử lý thanh toán")
      }
    } catch (error) {
      console.error("Process payment error:", error)
      alert("Có lỗi xảy ra khi xử lý thanh toán")
    }
  }

  async loadPromotions() {
    try {
      const response = await fetch("/api/promotions/active")
      const promotions = await response.json()

      const container = document.getElementById("promotionsList")
      if (container && promotions.length > 0) {
        container.innerHTML = promotions
          .map(
            (promo) => `
                    <div class="promotion-item" data-code="${promo.maKhuyenMai}" data-discount="${promo.giaTriGiam}" data-type="${promo.loaiGiam}">
                        <div class="promotion-info">
                            <h4>${promo.tenKhuyenMai}</h4>
                            <p>${promo.moTa}</p>
                            <span class="discount-value">
                                ${promo.loaiGiam === "Phần trăm" ? promo.giaTriGiam + "%" : promo.giaTriGiam.toLocaleString() + "đ"}
                            </span>
                        </div>
                    </div>
                `,
          )
          .join("")

        // Re-bind events for new elements
        document.querySelectorAll(".promotion-item").forEach((item) => {
          item.addEventListener("click", () => {
            this.selectPromotion(item)
          })
        })
      }
    } catch (error) {
      console.error("Load promotions error:", error)
    }
  }

  async loadLoyaltyPoints() {
    try {
      const response = await fetch("/api/loyalty/points")
      const data = await response.json()

      const pointsElement = document.getElementById("availablePoints")
      if (pointsElement) {
        pointsElement.textContent = data.points.toLocaleString()
      }
    } catch (error) {
      console.error("Load loyalty points error:", error)
    }
  }

  selectPromotion(item) {
    // Remove previous selection
    document.querySelectorAll(".promotion-item").forEach((el) => {
      el.classList.remove("selected")
    })

    // Add selection to clicked item
    item.classList.add("selected")

    // Calculate discount
    this.calculatePromotionDiscount()
  }

  calculatePromotionDiscount() {
    const selectedPromo = document.querySelector(".promotion-item.selected")
    const originalPrice = Number.parseFloat(document.getElementById("originalPrice").dataset.price)

    let discount = 0

    if (selectedPromo) {
      const discountValue = Number.parseFloat(selectedPromo.dataset.discount)
      const discountType = selectedPromo.dataset.type

      if (discountType === "Phần trăm") {
        discount = originalPrice * (discountValue / 100)
      } else {
        discount = discountValue
      }
    }

    this.updateTotalPrice(discount, "promotion")
  }

  calculateLoyaltyDiscount(points) {
    const maxPoints = Number.parseInt(document.getElementById("availablePoints").textContent.replace(/,/g, ""))
    const usedPoints = Math.min(Number.parseInt(points) || 0, maxPoints)

    // 1 point = 100 VND discount
    const discount = usedPoints * 100

    document.getElementById("loyaltyPointsInput").value = usedPoints
    this.updateTotalPrice(discount, "loyalty")
  }

  updateTotalPrice(discount, type) {
    const originalPrice = Number.parseFloat(document.getElementById("originalPrice").dataset.price)
    let totalDiscount = 0

    // Get current discounts
    const promotionDiscount = this.getPromotionDiscount()
    const loyaltyDiscount = this.getLoyaltyDiscount()

    if (type === "promotion") {
      totalDiscount = discount + loyaltyDiscount
    } else if (type === "loyalty") {
      totalDiscount = promotionDiscount + discount
    }

    const finalPrice = Math.max(0, originalPrice - totalDiscount)
    const earnedPoints = Math.floor(originalPrice * 0.0003) // 0.03% of original price

    // Update UI
    document.getElementById("totalDiscount").textContent = totalDiscount.toLocaleString() + "đ"
    document.getElementById("finalPrice").textContent = finalPrice.toLocaleString() + "đ"
    document.getElementById("earnedPoints").textContent = earnedPoints.toLocaleString()
  }

  getPromotionDiscount() {
    const selectedPromo = document.querySelector(".promotion-item.selected")
    if (!selectedPromo) return 0

    const originalPrice = Number.parseFloat(document.getElementById("originalPrice").dataset.price)
    const discountValue = Number.parseFloat(selectedPromo.dataset.discount)
    const discountType = selectedPromo.dataset.type

    if (discountType === "Phần trăm") {
      return originalPrice * (discountValue / 100)
    } else {
      return discountValue
    }
  }

  getLoyaltyDiscount() {
    const usedPoints = Number.parseInt(document.getElementById("loyaltyPointsInput").value) || 0
    return usedPoints * 100
  }

  getSelectedPromotion() {
    const selectedPromo = document.querySelector(".promotion-item.selected")
    return selectedPromo ? selectedPromo.dataset.code : null
  }

  getUsedLoyaltyPoints() {
    return Number.parseInt(document.getElementById("loyaltyPointsInput").value) || 0
  }

  updatePaymentMethod(method) {
    const methodInfo = document.getElementById("paymentMethodInfo")
    if (methodInfo) {
      let info = ""
      switch (method) {
        case "momo":
          info = "Thanh toán qua ví điện tử MoMo"
          break
        case "vnpay":
          info = "Thanh toán qua cổng VNPay"
          break
      }
      methodInfo.textContent = info
    }
  }

  async releaseSeats() {
    try {
      await fetch("/api/seats/release", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
      })
    } catch (error) {
      console.error("Release seats error:", error)
    }
  }
}

// Initialize payment manager when page loads
document.addEventListener("DOMContentLoaded", () => {
  new PaymentManager()
})
