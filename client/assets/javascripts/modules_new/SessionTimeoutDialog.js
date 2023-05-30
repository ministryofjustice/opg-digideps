// SESSION TIMEOUT POPUP LOGIC

const SessionTimeoutDialog = {
  init (options) {
    this.element = options.element
    this.popUpButton = options.okBtn
    this.sessionPopupShowAfterMs = options.sessionPopupShowAfterMs
    this.redirectAfterMs = options.sessionExpiresMs + 3000
    this.keepSessionAliveUrl = options.keepSessionAliveUrl

    const that = this

    this.popUpButton.addEventListener('click', function (event) {
      event.preventDefault()
      that.hidePopupAndRestartCountdown(that)
    })

    this.startCountdown(that)
  },

  startCountdown (that) {
    that.countDownPopupIntervalId = window.setInterval(
      function () {
        that.element.style.display = 'block'
      },
      that.sessionPopupShowAfterMs
    )

    that.countDownLogoutIntervalId = window.setInterval(
      function () {
        window.location.reload()
      },
      that.redirectAfterMs
    )
  },

  hidePopupAndRestartCountdown (that) {
    that.element.style.display = 'none'

    window.fetch(that.keepSessionAliveUrl + '?refresh=' + Date.now())

    // restart countdown
    window.clearInterval(that.countDownPopupIntervalId)
    window.clearInterval(that.countDownLogoutIntervalId)
    this.startCountdown(that)
  }
}

export default SessionTimeoutDialog
