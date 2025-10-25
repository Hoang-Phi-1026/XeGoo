document.addEventListener("DOMContentLoaded", () => {
  // Handle all rating forms on the page
  const ratingForms = document.querySelectorAll(".rating-form")

  ratingForms.forEach((form) => {
    // Character counter for textarea
    const textarea = form.querySelector(".rating-textarea")
    const charCount = form.querySelector("#charCount")

    if (textarea && charCount) {
      textarea.addEventListener("input", function () {
        charCount.textContent = this.value.length
      })
    }

    // Form submission
    form.addEventListener("submit", async (e) => {
      e.preventDefault()

      const submitBtn = form.querySelector(".btn-submit-rating")
      const originalText = submitBtn.innerHTML

      // Validate at least one rating is selected
      const serviceRating = form.querySelector('input[name="serviceRating"]:checked')
      const driverRating = form.querySelector('input[name="driverRating"]:checked')
      const vehicleRating = form.querySelector('input[name="vehicleRating"]:checked')

      if (!serviceRating || !driverRating || !vehicleRating) {
        showRatingError("Vui lòng đánh giá cả ba tiêu chí")
        return
      }

      // Disable button and show loading state
      submitBtn.disabled = true
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang gửi...'

      try {
        const formData = {
          tripId: form.querySelector('input[name="tripId"]').value,
          bookingId: form.querySelector('input[name="bookingId"]').value,
          serviceRating: serviceRating.value,
          driverRating: driverRating.value,
          vehicleRating: vehicleRating.value,
          comment: form.querySelector('textarea[name="comment"]').value,
        }

        const response = await fetch(window.BASE_URL + "/my-tickets/saveRating", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(formData),
        })

        const data = await response.json()

        if (data.success) {
          showRatingSuccess(data.message)
          form.reset()
          charCount.textContent = "0"

          // Disable form after successful submission
          setTimeout(() => {
            form.style.display = "none"
            const successMsg = document.createElement("div")
            successMsg.className = "rating-success"
            successMsg.innerHTML = '<i class="fas fa-check-circle"></i> Cảm ơn bạn đã đánh giá chuyến đi!'
            form.parentElement.appendChild(successMsg)
          }, 1500)
        } else {
          showRatingError(data.message || "Có lỗi xảy ra khi lưu đánh giá")
          submitBtn.disabled = false
          submitBtn.innerHTML = originalText
        }
      } catch (error) {
        console.error("Error:", error)
        showRatingError("Có lỗi xảy ra khi gửi đánh giá")
        submitBtn.disabled = false
        submitBtn.innerHTML = originalText
      }
    })
  })
})

function showRatingSuccess(message) {
  const notification = document.createElement("div")
  notification.className = "rating-success"
  notification.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`
  document.body.appendChild(notification)

  setTimeout(() => {
    notification.remove()
  }, 3000)
}

function showRatingError(message) {
  const notification = document.createElement("div")
  notification.className = "rating-error"
  notification.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`
  document.body.appendChild(notification)

  setTimeout(() => {
    notification.remove()
  }, 3000)
}
