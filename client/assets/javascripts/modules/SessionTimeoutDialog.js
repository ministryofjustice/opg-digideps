// SESSION TIMEOUT POPUP LOGIC
/**
 * @param element
 * @param sessionExpiresMs
 * @param sessionPopupShowAfterMs
 * @param refreshUrl
 */
const SessionTimeoutDialog = function (options) {
  const element = options.element
  const sessionExpiresMs = options.sessionExpiresMs
  const sessionPopupShowAfterMs = options.sessionPopupShowAfterMs
  const keepSessionAliveUrl = options.keepSessionAliveUrl
  const redirectAfterMs = 3000
  const okBtn = options.okBtn

  function startCountdown () {
    window.setInterval(() => {
      element.style.display = 'block'
    }, sessionPopupShowAfterMs)

    window.setInterval(() => {
      window.location.reload()
    }, sessionExpiresMs + redirectAfterMs)
  }

  // attach click event
  okBtn.addEventListener('click', function (e) {
    e.preventDefault()
    hidePopupAndRestartCountdown(element)
  })

  function hidePopupAndRestartCountdown (element) {
    element.style.display = 'none'

    keepSessionAlive()

    // restart countdown
    window.clearInterval(this.countDownPopup)
    window.clearInterval(this.countDownLogout)
    startCountdown()
  }

  function keepSessionAlive () {
    window.fetch(keepSessionAliveUrl + '?refresh=' + Date.now())
  }

  return {
    startCountdown: startCountdown
  }
}

export default SessionTimeoutDialog
