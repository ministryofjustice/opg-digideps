import { describe, expect, it, jest } from '@jest/globals'
import SessionTimeoutDialog from '../../modules_new/SessionTimeoutDialog'
import { findByText, queryByText } from '@testing-library/dom'
import { userEvent } from '@testing-library/user-event/dist/types/setup/index'

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
      expect(SessionTimeoutDialogObj.redirectAfterMs).toEqual(redirectAfterMs)
      expect(SessionTimeoutDialogObj.popUpButton).toEqual(buttonElement)
    })

    it('adds click eventListener to button', function () {
      const spy = jest.spyOn(buttonElement, 'addEventListener')

      SessionTimeoutDialogObj.init(validOptions)

      expect(spy).toHaveBeenCalledWith('click', SessionTimeoutDialogObj.onButtonClickHandler)
    })

    it('has displayed element after timer', async function () {
      SessionTimeoutDialogObj.init(validOptions)

      const elementVisible = await findByText('Test div')
      expect(elementVisible).toBeTruthy()
    })

    it('has hidden element after button click', async function () {
      SessionTimeoutDialogObj.init(validOptions)
      const user = userEvent.setup()
      const button = await findByText('Test btn')

      await user.click(button)

      const element = queryByText('Test div')

      expect(element).toBeNull()
    })
  })

  describe('hidePopupAndRestartCountdown', function () {
    document.body.innerHTML = '<div>Test div</div><button>Test btn</button>'
    const divElement = document.querySelector('div')
    const SessionTimeoutDialogObj = SessionTimeoutDialog

    it('hides the popup', function () {
      SessionTimeoutDialogObj.hidePopupAndRestartCountdown(SessionTimeoutDialogObj)

      expect(divElement.style.display).toEqual('none')
    })

    it('keeps the session alive', function () {
      const spy = jest.spyOn(SessionTimeoutDialogObj, 'keepSessionAlive')

      SessionTimeoutDialogObj.hidePopupAndRestartCountdown(SessionTimeoutDialogObj)

      expect(spy).toHaveBeenCalled()
    })

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
