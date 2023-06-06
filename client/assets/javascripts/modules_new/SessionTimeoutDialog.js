// SESSION TIMEOUT POPUP LOGIC

const SessionTimeoutDialog = {
  init: function (options) {
    this.element = options.element
    this.sessionExpiresMs = options.sessionExpiresMs
    this.sessionPopupShowAfterMs = options.sessionPopupShowAfterMs
    this.keepSessionAliveUrl = options.keepSessionAliveUrl
    this.redirectAfterMs = 3000
    this.okBtn = options.okBtn

    this.okBtn.addEventListener('click', this.onButtonClickHandler)
  },

  onButtonClickHandler: function (event) {
    event.preventDefault()
    this.hidePopupAndRestartCountdown(this.element)
  },

  startCountdown: function () {
    this.countDownPopupIntervalId = window.setInterval(
      this.displayElementBlock,
      this.sessionPopupShowAfterMs
    )

    this.countDownLogoutIntervalId = window.setInterval(
      this.reloadWindow,
      this.sessionExpiresMs + this.redirectAfterMs
    )
  },

  displayElementBlock: function () {
    this.element.style.display = 'block'
  },

  reloadWindow: function () {
    window.location.reload()
  },

  hidePopupAndRestartCountdown: function (element) {
    element.style.display = 'none'

    this.keepSessionAlive()

    // restart countdown
    window.clearInterval(this.countDownPopupIntervalId)
    window.clearInterval(this.countDownLogoutIntervalId)
    this.startCountdown()
  },

  keepSessionAlive: function () {
    window.fetch(this.keepSessionAliveUrl + '?refresh=' + Date.now())
  }
}

export default SessionTimeoutDialog
