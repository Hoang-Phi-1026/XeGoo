// Search Compatibility Layer
// This file ensures backward compatibility with existing search functionality

// Legacy function support for older code
window.loadCities = () => {
    if (window.unifiedSearch && window.unifiedSearch.loadCities) {
      return window.unifiedSearch.loadCities()
    }
    console.warn("UnifiedSearch not available, using fallback")
    return Promise.resolve([])
  }
  
  // Legacy trip type toggle support
  window.toggleTripType = (isRoundTrip) => {
    const tripTypeInput = document.querySelector(
      `input[name="trip_type"][value="${isRoundTrip ? "round_trip" : "one_way"}"]`,
    )
    if (tripTypeInput) {
      tripTypeInput.checked = true
      if (window.unifiedSearch && window.unifiedSearch.handleTripTypeChange) {
        window.unifiedSearch.handleTripTypeChange(tripTypeInput)
      }
    }
  }
  
  // Legacy date validation support
  window.validateDates = () => {
    const departureDateInput = document.querySelector('input[name="departure_date"]')
    if (departureDateInput && window.unifiedSearch && window.unifiedSearch.handleDepartureDateChange) {
      window.unifiedSearch.handleDepartureDateChange(departureDateInput)
    }
  }
  
  // Legacy form validation support
  window.validateSearchForm = (form) => {
    if (window.unifiedSearch && window.unifiedSearch.handleFormSubmit) {
      const event = { target: form, preventDefault: () => {} }
      return window.unifiedSearch.handleFormSubmit(event)
    }
    return true
  }
  
  // Legacy city swap support
  window.swapCities = () => {
    if (window.unifiedSearch && window.unifiedSearch.swapCities) {
      window.unifiedSearch.swapCities()
    }
  }
  
  // Legacy error/success message support
  window.showSearchError = (message) => {
    if (window.unifiedSearch && window.unifiedSearch.showError) {
      window.unifiedSearch.showError(message)
    } else if (window.showError) {
      window.showError(message)
    }
  }
  
  window.showSearchSuccess = (message) => {
    if (window.unifiedSearch && window.unifiedSearch.showSuccess) {
      window.unifiedSearch.showSuccess(message)
    } else if (window.showSuccess) {
      window.showSuccess(message)
    }
  }
  
  // Auto-initialize compatibility layer
  document.addEventListener("DOMContentLoaded", () => {
    // Wait for unified search to be ready
    const checkUnifiedSearch = () => {
      if (window.unifiedSearch) {
        console.log("[XeGoo] Search compatibility layer initialized")
  
        // Trigger any legacy initialization code
        const legacyInitEvent = new CustomEvent("legacySearchReady", {
          detail: { unifiedSearch: window.unifiedSearch },
        })
        document.dispatchEvent(legacyInitEvent)
      } else {
        setTimeout(checkUnifiedSearch, 100)
      }
    }
  
    checkUnifiedSearch()
  })
  
  // Export for module systems
  if (typeof module !== "undefined" && module.exports) {
    module.exports = {
      loadCities: window.loadCities,
      toggleTripType: window.toggleTripType,
      validateDates: window.validateDates,
      validateSearchForm: window.validateSearchForm,
      swapCities: window.swapCities,
      showSearchError: window.showSearchError,
      showSearchSuccess: window.showSearchSuccess,
    }
  }
  