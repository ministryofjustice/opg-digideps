import SessionTimeout from '../../globals/sessionTimeout'
import { describe, it } from '@jest/globals'
import SessionTimeoutDialog from '../../modules/SessionTimeoutDialog'

describe('SessionTimeout', () => {
  const validDocumentBody = () => {
    document.body.innerHTML = `
        <div data-session-expires="123"></div>
        <div data-popup-expires="456" ></div>
        <div data-keep-alive="http://www.example.org"></div>
        <div data-module="app-timeout-popup"></div>
        <button data-js="ok-button"></button>
      `
  }

  const removeDOMelementByDA = (da) => {
    const element = document.querySelector(`[${da}]`)
    element.parentNode.removeChild(element)
  }

  describe('when called on a page with all required markup', () => {
    it('passes extracted values to SessionTimeoutDialog', () => {
      const spy = jest.spyOn(SessionTimeoutDialog, 'init')

      validDocumentBody()
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

  describe('a human readable error is thrown', () => {
    const DAs = [
      'data-keep-alive',
      'data-session-expires',
      'data-popup-expires',
      'data-module="app-timeout-popup"',
      'data-js="ok-button"'
    ]

    DAs.forEach(da => {
      it(`when ${da} is missing from page`, () => {
        validDocumentBody()
        removeDOMelementByDA(da)

        expect(() => {
          SessionTimeout()
        }).toThrow(`${da} missing from the page - ensure it is a data attribute on a page element`)
      })
    })
  })
})
