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
  },

  startCountdown () {
    this.countDownPopup = window.setInterval(() => {
      this.element.style.display = 'block'
    }, this.sessionPopupShowAfterMs)

    this.countDownLogout = window.setInterval(() => {
      window.location.reload()
    }, this.sessionExpiresMs + this.redirectAfterMs)
  },

  addEventListener (okBtn, element) {
    // attach click event
    okBtn.addEventListener('click', function (e) {
      e.preventDefault()
      this.hidePopupAndRestartCountdown(element)
    })
  },

  hidePopupAndRestartCountdown (element) {
    element.style.display = 'none'

    this.keepSessionAlive()

    // restart countdown
    window.clearInterval(this.countDownPopup)
    window.clearInterval(this.countDownLogout)
    this.startCountdown()
  },

  keepSessionAlive () {
    window.fetch(this.keepSessionAliveUrl + '?refresh=' + Date.now())
  }
}

export default SessionTimeoutDialog
