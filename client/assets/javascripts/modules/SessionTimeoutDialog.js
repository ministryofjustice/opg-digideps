// SESSION TIMEOUT POPUP LOGIC
/**
 * @param element
 * @param sessionExpiresMs
 * @param sessionPopupShowAfterMs
 * @param refreshUrl
 */
const SessionTimeoutDialog = {
  init (options) {
    this.element = options.element
    this.sessionExpiresMs = options.sessionExpiresMs
    this.sessionPopupShowAfterMs = options.sessionPopupShowAfterMs
    this.keepSessionAliveUrl = options.keepSessionAliveUrl
    this.redirectAfterMs = 3000
    this.okBtn = options.okBtn

    this.okBtn.addEventListener('click', this.onButtonClickHandler)
  },

  onButtonClickHandler (event) {
    event.preventDefault()
    this.hidePopupAndRestartCountdown(this.element)
  },

  startCountdown () {
    this.countDownPopupIntervalId = window.setInterval(
      this.displayElementBlock,
      this.sessionPopupShowAfterMs
    )

    this.countDownLogoutIntervalId = window.setInterval(
      this.reloadWindow,
      this.sessionExpiresMs + this.redirectAfterMs
    )
  },

  displayElementBlock () {
    this.element.style.display = 'block'
  },

  reloadWindow () {
    window.location.reload()
  },

  hidePopupAndRestartCountdown (element) {
    element.style.display = 'none'

    this.keepSessionAlive()

    // restart countdown
    window.clearInterval(this.countDownPopupIntervalId)
    window.clearInterval(this.countDownLogoutIntervalId)
    this.startCountdown()
  },

  keepSessionAlive () {
    window.fetch(this.keepSessionAliveUrl + '?refresh=' + Date.now())
  }
}

export default SessionTimeoutDialog
