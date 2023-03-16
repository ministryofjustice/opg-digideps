import { describe, expect, it, jest } from '@jest/globals'
import SessionTimeoutDialog from '../../modules_new/SessionTimeoutDialog'

// Required to test against .fetch API
require('jest-fetch-mock').enableMocks()

describe('SessionTimeoutDialog', () => {
  describe('init', () => {
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

    it('assigns values from options to properties', () => {
      SessionTimeoutDialog.init(validOptions)

      expect(SessionTimeoutDialog.element).toEqual(divElement)
      expect(SessionTimeoutDialog.sessionExpiresMs).toEqual(sessionExpiresMs)
      expect(SessionTimeoutDialog.sessionPopupShowAfterMs).toEqual(sessionPopupShowAfterMs)
      expect(SessionTimeoutDialog.keepSessionAliveUrl).toEqual(keepSessionAliveUrl)
      expect(SessionTimeoutDialog.redirectAfterMs).toEqual(redirectAfterMs)
      expect(SessionTimeoutDialog.okBtn).toEqual(buttonElement)
    })

    it('adds click eventListener to button', () => {
      const spy = jest.spyOn(buttonElement, 'addEventListener')

      SessionTimeoutDialog.init(validOptions)

      expect(spy).toHaveBeenCalledWith('click', SessionTimeoutDialog.onButtonClickHandler)
    })
  })

  describe('startCountdown', () => {
    describe('sets intervals for showing popup and reload', () => {
      document.body.innerHTML = '<div>Test div</div><button>Test btn</button>'
      const divElement = document.querySelector('div')

      SessionTimeoutDialog.element = divElement
      SessionTimeoutDialog.sessionPopupShowAfterMs = 500
      SessionTimeoutDialog.sessionExpiresMs = 400
      SessionTimeoutDialog.redirectAfterMs = 300

      const spy = jest.spyOn(window, 'setInterval')

      SessionTimeoutDialog.startCountdown()

      expect(spy).toHaveBeenNthCalledWith(1, SessionTimeoutDialog.displayElementBlock, 500)
      expect(spy).toHaveBeenNthCalledWith(2, SessionTimeoutDialog.reloadWindow, 700)
    })
  })

  describe('hidePopupAndRestartCountdown', () => {
    document.body.innerHTML = '<div>Test div</div><button>Test btn</button>'
    const divElement = document.querySelector('div')

    it('hides the popup', () => {
      SessionTimeoutDialog.hidePopupAndRestartCountdown(divElement)

      expect(divElement.style.display).toEqual('none')
    })

    it('keeps the session alive', () => {
      const spy = jest.spyOn(SessionTimeoutDialog, 'keepSessionAlive')

      SessionTimeoutDialog.hidePopupAndRestartCountdown(divElement)

      expect(spy).toHaveBeenCalled()
    })

    it('clears the window intervals', () => {
      const spy = jest.spyOn(window, 'clearInterval')

      SessionTimeoutDialog.countDownPopupIntervalId = 'abc'
      SessionTimeoutDialog.countDownLogoutIntervalId = 'xyz'

      SessionTimeoutDialog.hidePopupAndRestartCountdown(divElement)

      expect(spy).toHaveBeenNthCalledWith(1, 'abc')
      expect(spy).toHaveBeenNthCalledWith(2, 'xyz')
    })

    it('starts the countdown again', () => {
      const spy = jest.spyOn(SessionTimeoutDialog, 'startCountdown')

      SessionTimeoutDialog.hidePopupAndRestartCountdown(divElement)

      expect(spy).toHaveBeenCalled()
    })
  })

  describe('keepSessionAlive', () => {
    it('makes a get request to keepSessionAliveUrl', () => {
      const spy = jest.spyOn(window, 'fetch')
      SessionTimeoutDialog.keepSessionAliveUrl = 'example/url'

      SessionTimeoutDialog.keepSessionAlive()

      expect(spy).toHaveBeenCalledWith('example/url?refresh=' + Date.now())
    })
  })
})
