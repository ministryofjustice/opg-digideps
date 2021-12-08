import { SessionTimeoutDialog } from '../modules/SessionTimeoutDialog'

export const SessionTimeout = function () {
  const sessionExpiresValue = document.querySelector('[data-session-expires]').dataset.sessionExpires
  const popupExpiresValue = document.querySelector('[data-popup-expires]').dataset.popupExpires
  const keepAliveUrl = document.querySelector('[data-keep-alive]').dataset.keepAlive

  const appTimeoutPop = document.querySelector('[data-module="app-timeout-popup"]')
  const okBtn = document.querySelector('[data-js]')

  console.log('hello, world')
  SessionTimeoutDialog({
    element: appTimeoutPop,
    sessionExpiresMs: sessionExpiresValue * 1000,
    sessionPopupShowAfterMs: popupExpiresValue * 1000,
    keepSessionAliveUrl: keepAliveUrl,
    okBtn: okBtn
  }).startCountdown()
}
