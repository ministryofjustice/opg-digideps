import SessionTimeoutDialog from '../modules/SessionTimeoutDialog'

const getDataAttributeData = function (dataAttributeName) {
  const element = document.querySelector(`[${dataAttributeName}]`)

  if (element === null) {
    console.log(`${dataAttributeName} missing from the page - ensure it is a data attribute on a page element`)
    return null
  }

  return element.getAttribute(dataAttributeName)
}

const getElementByDataAttribute = function (dataAttribute) {
  const element = document.querySelector(`[${dataAttribute}]`)

  if (element === null) {
    console.log(`${dataAttribute} missing from the page - ensure it is a data attribute on a page element`)
    return null
  }

  return element
}

const SessionTimeout = function () {
  const keepAliveUrl = getDataAttributeData('data-keep-alive')
  const sessionExpiresValue = getDataAttributeData('data-session-expires')
  const popupExpiresValue = getDataAttributeData('data-popup-expires')

  const appTimeoutPop = getElementByDataAttribute('data-module="app-timeout-popup"')
  const okBtn = getElementByDataAttribute('data-js="ok-button"')

  if ([keepAliveUrl, sessionExpiresValue, popupExpiresValue, appTimeoutPop, okBtn].includes(null)) {
    console.log('Required data or element is missing from page')
    return null
  }

  SessionTimeoutDialog.init({
    element: appTimeoutPop,
    sessionExpiresMs: sessionExpiresValue * 1000,
    sessionPopupShowAfterMs: popupExpiresValue * 1000,
    keepSessionAliveUrl: keepAliveUrl,
    okBtn: okBtn
  })

  SessionTimeoutDialog.startCountdown()
}

export default SessionTimeout
