document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("ticketLookupForm")
  const resultSection = document.getElementById("ticketResult")
  const errorSection = document.getElementById("errorMessage")
  const closeResultBtn = document.getElementById("closeResult")

  // Handle form submission
  form.addEventListener("submit", async (e) => {
    e.preventDefault()

    // Hide previous results/errors
    resultSection.style.display = "none"
    errorSection.style.display = "none"

    // Get form data
    const formData = new FormData(form)
    const submitBtn = form.querySelector('button[type="submit"]')

    // Disable submit button
    submitBtn.disabled = true
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tra cứu...'

    try {
      const response = await fetch(window.BASE_URL + "/ticket-lookup/search", {
        method: "POST",
        body: formData,
      })

      const data = await response.json()

      if (data.success) {
        displayTicket(data.ticket)
        // Scroll to result
        resultSection.scrollIntoView({ behavior: "smooth", block: "start" })
      } else {
        showError(data.message)
      }
    } catch (error) {
      console.error("[v0] Ticket lookup error:", error)
      showError("Có lỗi xảy ra khi tra cứu vé. Vui lòng thử lại sau.")
    } finally {
      // Re-enable submit button
      submitBtn.disabled = false
      submitBtn.innerHTML = '<i class="fas fa-search"></i> Tra Cứu Vé'
    }
  })

  // Handle close result button
  closeResultBtn.addEventListener("click", () => {
    resultSection.style.display = "none"
    form.reset()
    // Scroll back to form
    form.scrollIntoView({ behavior: "smooth", block: "start" })
  })

  // Display ticket information
  function displayTicket(ticket) {
    // Route information
    document.getElementById("routeCode").textContent = ticket.kyHieuTuyen
    document.getElementById("cityFrom").textContent = ticket.diemDi
    document.getElementById("cityTo").textContent = ticket.diemDen

    // Status
    const statusElement = document.getElementById("ticketStatus")
    if (ticket.trangThaiDatVe === "DaHuy") {
      statusElement.textContent = "Đã hủy"
      statusElement.className = "ticket-status status-cancelled"
    } else {
      statusElement.textContent = "Đã thanh toán"
      statusElement.className = "ticket-status status-active"
    }

    // Trip details
    document.getElementById("vehicleType").textContent = ticket.tenLoaiPhuongTien
    document.getElementById("licensePlate").textContent = ticket.bienSo
    document.getElementById("seatCapacity").textContent = `${ticket.soChoMacDinh} chỗ`
    document.getElementById("departureDate").textContent = ticket.ngayKhoiHanh
    document.getElementById("departureTime").textContent = ticket.gioKhoiHanh
    document.getElementById("arrivalTime").textContent = ticket.gioKetThuc

    // Passenger information
    document.getElementById("passengerName").textContent = ticket.hoTenHanhKhach
    document.getElementById("passengerEmail").textContent = ticket.emailHanhKhach || "Không có"
    document.getElementById("passengerPhone").textContent = ticket.soDienThoaiHanhKhach || "Không có"
    document.getElementById("pickupPoint").textContent = ticket.diemDonTen || "Không có"
    document.getElementById("dropoffPoint").textContent = ticket.diemTraTen || "Không có"

    // QR Code
    const qrContainer = document.getElementById("qrCodeContainer")
    qrContainer.innerHTML = `<img src="${ticket.qrCode}" alt="QR Code">`

    // Ticket code
    document.getElementById("ticketCodeDisplay").textContent = ticket.maChiTiet

    // Show result section
    resultSection.style.display = "block"
  }

  // Show error message
  function showError(message) {
    document.getElementById("errorText").textContent = message
    errorSection.style.display = "flex"

    // Scroll to error
    errorSection.scrollIntoView({ behavior: "smooth", block: "center" })

    // Auto hide after 5 seconds
    setTimeout(() => {
      errorSection.style.display = "none"
    }, 5000)
  }
})
