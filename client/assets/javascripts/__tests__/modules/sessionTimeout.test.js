import SessionTimeout from '../../globals/sessionTimeout'
import { describe, it } from '@jest/globals'
import SessionTimeoutDialog from '../../modules/SessionTimeoutDialog'

describe('SessionTimeout', () => {
  describe('when called on a page with all required markup', () => {
    const documentBody = () => {
      document.body.innerHTML = `
        <div data-session-expires="123" data-popup-expires="456" data-keep-alive="http://www.example.org"></div>
        <div data-module="app-timeout-popup"></div>
        <button data-js="ok-button"></button>
      `
    }

    it('passes extracted values to SessionTimeoutDialog', () => {
      const spy = jest.spyOn(SessionTimeoutDialog, 'init')

      documentBody()
      SessionTimeout()

      const popupDiv = document.querySelector('[data-module="app-timeout-popup"]')
      const popupButton = document.querySelector('[data-js="ok-button"]')

      const expectedOptions = {
        element: popupDiv,
        sessionExpiresMs: 123 * 1000,
        sessionPopupShowAfterMs: 456 * 1000,
        keepSessionAliveUrl: 'http://www.example.org',
        okBtn: popupButton
      }

      expect(spy).toHaveBeenCalledWith(expectedOptions)
    })
  })
})
