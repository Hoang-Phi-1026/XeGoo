// Theme Toggle Functionality
class ThemeToggle {
    constructor() {
      this.themeToggle = document.querySelector(".theme-toggle")
      this.themeIcon = this.themeToggle?.querySelector("i")
      this.currentTheme = this.getStoredTheme() || this.getPreferredTheme()
  
      this.init()
    }
  
    init() {
      // Set initial theme
      this.setTheme(this.currentTheme)
  
      // Add event listener
      if (this.themeToggle) {
        this.themeToggle.addEventListener("click", () => {
          this.toggleTheme()
        })
      }
  
      // Listen for system theme changes
      window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change", (e) => {
        if (!this.getStoredTheme()) {
          this.setTheme(e.matches ? "dark" : "light")
        }
      })
    }
  
    getStoredTheme() {
      return localStorage.getItem("theme")
    }
  
    getPreferredTheme() {
      return window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light"
    }
  
    setTheme(theme) {
      this.currentTheme = theme
      document.documentElement.setAttribute("data-theme", theme)
      localStorage.setItem("theme", theme)
      this.updateIcon(theme)
      this.updateMetaThemeColor(theme)
    }
  
    updateIcon(theme) {
      if (!this.themeIcon) return
  
      // Add transition class for smooth icon change
      this.themeIcon.style.transform = "scale(0)"
  
      setTimeout(() => {
        if (theme === "dark") {
          this.themeIcon.className = "fas fa-sun"
          this.themeToggle.title = "Chuyển sang chế độ sáng"
        } else {
          this.themeIcon.className = "fas fa-moon"
          this.themeToggle.title = "Chuyển sang chế độ tối"
        }
        this.themeIcon.style.transform = "scale(1)"
      }, 150)
    }
  
    updateMetaThemeColor(theme) {
      // Update meta theme color for mobile browsers
      let metaThemeColor = document.querySelector('meta[name="theme-color"]')
      if (!metaThemeColor) {
        metaThemeColor = document.createElement("meta")
        metaThemeColor.name = "theme-color"
        document.head.appendChild(metaThemeColor)
      }
  
      const colors = {
        light: "#f8fafc", // bg-secondary in light mode
        dark: "#0b1d33", // bg-secondary in dark mode
      }
  
      metaThemeColor.content = colors[theme]
    }
  
    toggleTheme() {
      const newTheme = this.currentTheme === "light" ? "dark" : "light"
      this.setTheme(newTheme)
  
      // Add a subtle animation to the page
      document.body.style.transition = "background-color 0.3s ease, color 0.3s ease"
      setTimeout(() => {
        document.body.style.transition = ""
      }, 300)
  
      // Dispatch custom event for other components to listen to
      window.dispatchEvent(
        new CustomEvent("themeChanged", {
          detail: { theme: newTheme },
        }),
      )
    }
  
    // Public method to get current theme
    getCurrentTheme() {
      return this.currentTheme
    }
  }
  
  // Initialize theme toggle when DOM is loaded
  document.addEventListener("DOMContentLoaded", () => {
    window.themeToggle = new ThemeToggle()
  })
  
  // Export for use in other scripts
  if (typeof module !== "undefined" && module.exports) {
    module.exports = ThemeToggle
  }
  