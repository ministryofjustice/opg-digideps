import { describe, expect, it, jest } from '@jest/globals'
import SessionTimeoutDialog from '../../modules_new/SessionTimeoutDialog'

// Required to test against .fetch API
require('jest-fetch-mock').enableMocks()

describe('SessionTimeoutDialog', function () {
  describe('init', function () {
    document.body.innerHTML = '<div style="display: none">Test div</div><button>Test btn</button>'
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

    const SessionTimeoutDialogObj = SessionTimeoutDialog

    it('assigns values from options to properties', function () {
      SessionTimeoutDialogObj.init(validOptions)

      expect(SessionTimeoutDialogObj.element).toEqual(divElement)
      expect(SessionTimeoutDialogObj.sessionPopupShowAfterMs).toEqual(sessionPopupShowAfterMs)
      expect(SessionTimeoutDialogObj.keepSessionAliveUrl).toEqual(keepSessionAliveUrl)
      expect(SessionTimeoutDialogObj.redirectAfterMs).toEqual(redirectAfterMs + 1000)
      expect(SessionTimeoutDialogObj.popUpButton).toEqual(buttonElement)
    })

    it('adds click eventListener to button', function () {
      const spy = jest.spyOn(buttonElement, 'addEventListener')

      SessionTimeoutDialogObj.init(validOptions)

      expect(spy).toHaveBeenCalledWith('click', expect.any(Function))
    })
  })

  describe('hidePopupAndRestartCountdown', function () {
    document.body.innerHTML = '<div>Test div</div><button>Test btn</button>'
    const SessionTimeoutDialogObj = SessionTimeoutDialog

    it('clears the window intervals', function () {
      const spy = jest.spyOn(window, 'clearInterval')

      SessionTimeoutDialogObj.countDownPopupIntervalId = 'abc'
      SessionTimeoutDialogObj.countDownLogoutIntervalId = 'xyz'

      SessionTimeoutDialogObj.hidePopupAndRestartCountdown(SessionTimeoutDialogObj)

      expect(spy).toHaveBeenNthCalledWith(1, 'abc')
      expect(spy).toHaveBeenNthCalledWith(2, 'xyz')
    })

    it('starts the countdown again', function () {
      const spy = jest.spyOn(SessionTimeoutDialog, 'startCountdown')

      SessionTimeoutDialogObj.hidePopupAndRestartCountdown(SessionTimeoutDialogObj)

      expect(spy).toHaveBeenCalled()
    })

    it('makes a get request using keepSessionAliveUrl', function () {
      const spy = jest.spyOn(window, 'fetch')
      SessionTimeoutDialogObj.keepSessionAliveUrl = 'example/url'

      SessionTimeoutDialogObj.hidePopupAndRestartCountdown(SessionTimeoutDialogObj)

      expect(spy).toHaveBeenCalledWith('example/url?refresh=' + Date.now())
    })
  })
})
