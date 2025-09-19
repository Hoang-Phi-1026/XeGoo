// Unified Search System JavaScript
class UnifiedSearch {
    constructor() {
      this.baseUrl = window.BASE_URL || ""
      this.cities = []
      this.isLoading = false
      this.init()
    }
  
    init() {
      this.loadCities()
      this.bindEvents()
      this.initializeDateInputs()
      this.restoreFormState()
    }
  
    // Load cities from API
    async loadCities() {
      try {
        const response = await fetch(`${this.baseUrl}/search/cities`)
        if (!response.ok) throw new Error("Failed to load cities")
  
        this.cities = await response.json()
        this.populateCitySelects()
      } catch (error) {
        console.error("Error loading cities:", error)
        // Fallback to static cities
        this.cities = [
          { id: "Hà Nội", name: "Hà Nội" },
          { id: "TP. Hồ Chí Minh", name: "TP. Hồ Chí Minh" },
          { id: "Đà Nẵng", name: "Đà Nẵng" },
          { id: "Hải Phòng", name: "Hải Phòng" },
          { id: "Cần Thơ", name: "Cần Thơ" },
          { id: "Nha Trang", name: "Nha Trang" },
          { id: "Đà Lạt", name: "Đà Lạt" },
          { id: "Vũng Tàu", name: "Vũng Tàu" },
          { id: "Long An", name: "Long An" },
          { id: "Tây Ninh", name: "Tây Ninh" },
        ]
        this.populateCitySelects()
      }
    }
  
    // Populate city select dropdowns
    populateCitySelects() {
      const fromSelects = document.querySelectorAll('select[name="from"]')
      const toSelects = document.querySelectorAll('select[name="to"]')
      ;[fromSelects, toSelects].forEach((selects) => {
        selects.forEach((select) => {
          // Clear existing options except the first one
          while (select.children.length > 1) {
            select.removeChild(select.lastChild)
          }
  
          // Add city options
          this.cities.forEach((city) => {
            const option = document.createElement("option")
            option.value = city.id
            option.textContent = city.name
            select.appendChild(option)
          })
        })
      })
  
      // Restore selected values if they exist
      this.restoreSelectedCities()
    }
  
    // Bind event listeners
    bindEvents() {
      // Trip type toggle
      document.addEventListener("change", (e) => {
        if (e.target.name === "trip_type") {
          this.handleTripTypeChange(e.target)
        }
      })
  
      // Date validation
      document.addEventListener("change", (e) => {
        if (e.target.name === "departure_date") {
          this.handleDepartureDateChange(e.target)
        }
      })
  
      // Form submission
      document.addEventListener("submit", (e) => {
        if (e.target.matches("#searchForm, .search-form")) {
          this.handleFormSubmit(e)
        }
      })
  
      // Recent search items
      document.addEventListener("click", (e) => {
        if (e.target.closest(".recent-search-item")) {
          this.handleRecentSearchClick(e.target.closest(".recent-search-item"))
        }
      })
  
      // Filter changes
      document.addEventListener("change", (e) => {
        if (e.target.closest(".filter-sidebar")) {
          this.handleFilterChange()
        }
      })
  
      // Clear filters
      document.addEventListener("click", (e) => {
        if (e.target.matches(".filter-clear")) {
          e.preventDefault()
          this.clearFilters()
        }
      })
  
      // City swap functionality
      document.addEventListener("click", (e) => {
        if (e.target.matches(".swap-cities")) {
          e.preventDefault()
          this.swapCities()
        }
      })
  
      // Tab switching functionality
      document.addEventListener("click", (e) => {
        if (e.target.matches(".results-tab")) {
          e.preventDefault()
          this.handleTabSwitch(e.target)
        }
      })
    }
  
    // Handle trip type change
    handleTripTypeChange(input) {
      const returnDateGroups = document.querySelectorAll(".return-date-group, #returnDateGroup")
      const returnDateInputs = document.querySelectorAll('input[name="return_date"]')
      const isRoundTripInputs = document.querySelectorAll('input[name="is_round_trip"]')
  
      const isRoundTrip = input.value === "round_trip"
  
      returnDateGroups.forEach((group) => {
        group.style.display = isRoundTrip ? "block" : "none"
      })
  
      returnDateInputs.forEach((input) => {
        input.required = isRoundTrip
        if (!isRoundTrip) input.value = ""
      })
  
      isRoundTripInputs.forEach((input) => {
        input.value = isRoundTrip ? "1" : "0"
      })
    }
  
    // Handle departure date change
    handleDepartureDateChange(input) {
      const returnDateInputs = document.querySelectorAll('input[name="return_date"]')
      const departureDate = new Date(input.value)
      const nextDay = new Date(departureDate)
      nextDay.setDate(nextDay.getDate() + 1)
  
      const minReturnDate = nextDay.toISOString().split("T")[0]
  
      returnDateInputs.forEach((returnInput) => {
        returnInput.min = minReturnDate
  
        // Clear return date if it's before or equal to departure date
        if (returnInput.value && new Date(returnInput.value) <= departureDate) {
          returnInput.value = ""
        }
      })
    }
  
    // Initialize date inputs with proper min values
    initializeDateInputs() {
      const today = new Date().toISOString().split("T")[0]
      const departureDateInputs = document.querySelectorAll('input[name="departure_date"]')
  
      departureDateInputs.forEach((input) => {
        if (!input.value) {
          input.value = today
        }
        input.min = today
      })
    }
  
    // Handle form submission
    handleFormSubmit(e) {
      const form = e.target
      const formData = new FormData(form)
  
      // Basic validation
      const from = formData.get("from")
      const to = formData.get("to")
      const departureDate = formData.get("departure_date")
      const isRoundTrip = formData.get("is_round_trip") === "1"
      const returnDate = formData.get("return_date")
  
      const errors = []
  
      if (!from) errors.push("Vui lòng chọn điểm đi")
      if (!to) errors.push("Vui lòng chọn điểm đến")
      if (!departureDate) errors.push("Vui lòng chọn ngày đi")
      if (from === to) errors.push("Điểm đi và điểm đến không thể giống nhau")
      if (new Date(departureDate) < new Date().setHours(0, 0, 0, 0)) {
        errors.push("Ngày đi không thể là ngày trong quá khứ")
      }
      if (isRoundTrip) {
        if (!returnDate) errors.push("Vui lòng chọn ngày về")
        else if (new Date(returnDate) <= new Date(departureDate)) {
          errors.push("Ngày về phải sau ngày đi")
        }
      }
  
      if (errors.length > 0) {
        e.preventDefault()
        this.showError(errors.join("\n"))
        return false
      }
  
      // Save form state
      this.saveFormState(formData)
  
      // Show loading state
      this.showLoading()
  
      return true
    }
  
    // Handle recent search click
    handleRecentSearchClick(item) {
      const route = item.querySelector(".recent-search-route").textContent
      const [from, to] = route.split(" - ")
  
      // Fill form with recent search data
      const fromSelects = document.querySelectorAll('select[name="from"]')
      const toSelects = document.querySelectorAll('select[name="to"]')
  
      fromSelects.forEach((select) => {
        const option = Array.from(select.options).find((opt) => opt.text === from)
        if (option) select.value = option.value
      })
  
      toSelects.forEach((select) => {
        const option = Array.from(select.options).find((opt) => opt.text === to)
        if (option) select.value = option.value
      })
    }
  
    // Handle filter changes
    handleFilterChange() {
      // Auto-submit filter form after a short delay
      clearTimeout(this.filterTimeout)
      this.filterTimeout = setTimeout(() => {
        const filterForm = document.getElementById("filterForm")
        if (filterForm) {
          filterForm.submit()
        }
      }, 500)
    }
  
    // Clear all filters
    clearFilters() {
      const filterForm = document.getElementById("filterForm")
      if (!filterForm) return
  
      // Reset all radio buttons to default
      const radios = filterForm.querySelectorAll('input[type="radio"]')
      radios.forEach((radio) => {
        radio.checked = radio.value === ""
      })
  
      // Reset number inputs
      const numbers = filterForm.querySelectorAll('input[type="number"]')
      numbers.forEach((input) => {
        input.value = ""
      })
  
      // Submit form
      filterForm.submit()
    }
  
    // Swap cities
    swapCities() {
      const fromSelects = document.querySelectorAll('select[name="from"]')
      const toSelects = document.querySelectorAll('select[name="to"]')
  
      fromSelects.forEach((fromSelect, index) => {
        const toSelect = toSelects[index]
        if (toSelect) {
          const fromValue = fromSelect.value
          const toValue = toSelect.value
  
          fromSelect.value = toValue
          toSelect.value = fromValue
        }
      })
    }
  
    // Save form state to localStorage
    saveFormState(formData) {
      const state = {
        from: formData.get("from"),
        to: formData.get("to"),
        departure_date: formData.get("departure_date"),
        return_date: formData.get("return_date"),
        trip_type: formData.get("trip_type"),
        passengers: formData.get("passengers"),
        timestamp: Date.now(),
      }
  
      localStorage.setItem("searchFormState", JSON.stringify(state))
    }
  
    // Restore form state from localStorage
    restoreFormState() {
      try {
        const saved = localStorage.getItem("searchFormState")
        if (!saved) return
  
        const state = JSON.parse(saved)
  
        // Only restore if saved within last hour
        if (Date.now() - state.timestamp > 3600000) {
          localStorage.removeItem("searchFormState")
          return
        }
  
        // Restore form values
        Object.keys(state).forEach((key) => {
          if (key === "timestamp") return
  
          const inputs = document.querySelectorAll(`[name="${key}"]`)
          inputs.forEach((input) => {
            if (input.type === "radio") {
              input.checked = input.value === state[key]
            } else {
              input.value = state[key]
            }
          })
        })
  
        // Trigger trip type change if needed
        if (state.trip_type) {
          const tripTypeInput = document.querySelector(`input[name="trip_type"][value="${state.trip_type}"]`)
          if (tripTypeInput) {
            this.handleTripTypeChange(tripTypeInput)
          }
        }
      } catch (error) {
        console.error("Error restoring form state:", error)
        localStorage.removeItem("searchFormState")
      }
    }
  
    // Restore selected cities after populating dropdowns
    restoreSelectedCities() {
      // Get values from URL parameters
      const urlParams = new URLSearchParams(window.location.search)
      const fromValue = urlParams.get("from")
      const toValue = urlParams.get("to")
  
      if (fromValue) {
        document.querySelectorAll('select[name="from"]').forEach((select) => {
          select.value = fromValue
        })
      }
  
      if (toValue) {
        document.querySelectorAll('select[name="to"]').forEach((select) => {
          select.value = toValue
        })
      }
    }
  
    // Show loading state
    showLoading() {
      const buttons = document.querySelectorAll(".search-button")
      buttons.forEach((button) => {
        button.disabled = true
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tìm kiếm...'
      })
    }
  
    // Show error message
    showError(message) {
      // Create or update error display
      let errorDiv = document.querySelector(".search-error")
      if (!errorDiv) {
        errorDiv = document.createElement("div")
        errorDiv.className = "search-error"
        errorDiv.style.cssText = `
                  background: #fee2e2;
                  border: 1px solid #fecaca;
                  color: #dc2626;
                  padding: 1rem;
                  border-radius: 0.5rem;
                  margin: 1rem 0;
                  font-size: 0.875rem;
              `
  
        const form = document.querySelector(".search-form-container")
        if (form) {
          form.appendChild(errorDiv)
        }
      }
  
      errorDiv.textContent = message
      errorDiv.scrollIntoView({ behavior: "smooth", block: "nearest" })
  
      // Auto-hide after 5 seconds
      setTimeout(() => {
        if (errorDiv.parentNode) {
          errorDiv.remove()
        }
      }, 5000)
    }
  
    // Show success message
    showSuccess(message) {
      let successDiv = document.querySelector(".search-success")
      if (!successDiv) {
        successDiv = document.createElement("div")
        successDiv.className = "search-success"
        successDiv.style.cssText = `
                  background: #dcfce7;
                  border: 1px solid #bbf7d0;
                  color: #166534;
                  padding: 1rem;
                  border-radius: 0.5rem;
                  margin: 1rem 0;
                  font-size: 0.875rem;
              `
  
        const form = document.querySelector(".search-form-container")
        if (form) {
          form.appendChild(successDiv)
        }
      }
  
      successDiv.textContent = message
  
      // Auto-hide after 3 seconds
      setTimeout(() => {
        if (successDiv.parentNode) {
          successDiv.remove()
        }
      }, 3000)
    }
  
    // Handle tab switching
    handleTabSwitch(clickedTab) {
      const allTabs = document.querySelectorAll(".results-tab")
      const tabContent = document.querySelectorAll(".tab-content")
  
      // Remove active class from all tabs
      allTabs.forEach((tab) => tab.classList.remove("active"))
  
      // Add active class to clicked tab
      clickedTab.classList.add("active")
  
      // Get tab type (outbound or return)
      const tabType = clickedTab.dataset.tab || (clickedTab.textContent.includes("CHUYẾN ĐI") ? "outbound" : "return")
  
      // Show/hide appropriate content
      tabContent.forEach((content) => {
        if (content.dataset.tab === tabType) {
          content.style.display = "block"
        } else {
          content.style.display = "none"
        }
      })
  
      // Update URL without page reload
      const url = new URL(window.location)
      url.searchParams.set("tab", tabType)
      window.history.replaceState({}, "", url)
    }
  }
  
  // Initialize when DOM is loaded
  document.addEventListener("DOMContentLoaded", () => {
    window.unifiedSearch = new UnifiedSearch()
  })
  
  // Export for use in other scripts
  if (typeof module !== "undefined" && module.exports) {
    module.exports = UnifiedSearch
  }
  