import { describe, it } from '@jest/globals'
import SessionTimeoutDialog from '../../modules/SessionTimeoutDialog'

describe('SessionTimeoutDialog', () => {
  describe('init', () => {
    it('assigns values from options to properties', () => {
      document.body.innerHTML = '<div>Test div</div><button>Test btn</button>'
      const divElement = document.querySelector('div')
      const buttonElement = document.querySelector('button')
      const sessionExpiresMs = 1000
      const sessionPopupShowAfterMs = 2000
      const keepSessionAliveUrl = '/test/url'
      const redirectAfterMs = 3000

      const validOptions = {
        element: divElement,
        sessionExpiresMs: sessionExpiresMs,
        sessionPopupShowAfterMs: sessionPopupShowAfterMs,
        keepSessionAliveUrl: keepSessionAliveUrl,
        okBtn: buttonElement
      }

      SessionTimeoutDialog.init(validOptions)

      expect(SessionTimeoutDialog.element).toEqual(divElement)
      expect(SessionTimeoutDialog.sessionExpiresMs).toEqual(sessionExpiresMs)
      expect(SessionTimeoutDialog.sessionPopupShowAfterMs).toEqual(sessionPopupShowAfterMs)
      expect(SessionTimeoutDialog.keepSessionAliveUrl).toEqual(keepSessionAliveUrl)
      expect(SessionTimeoutDialog.redirectAfterMs).toEqual(redirectAfterMs)
      expect(SessionTimeoutDialog.okBtn).toEqual(buttonElement)
    })
  })
})
