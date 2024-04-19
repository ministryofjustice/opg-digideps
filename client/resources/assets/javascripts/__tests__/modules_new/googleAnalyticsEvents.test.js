import { GoogleAnalyticsEvents } from '../../modules_new/googleAnalyticsEvents'
import { beforeAll, describe, expect, it, jest } from '@jest/globals'

const globals = (() => {
  window.gtag = jest.fn()

  function gtagWrapper (event, eventName, eventParameters) {
    window.gtag(event, eventName, eventParameters)
  }

  return {
    gtag: gtagWrapper
  }
})()

window.globals = globals

const setDocumentBody = () => {
  document.body.innerHTML = `
        <div>
            <button
              id='button1'
              data-attribute="ga-event"
              data-ga-action="form-submitted"
              data-ga-category="user-journeys"
              data-ga-label="button-clicks"
            >1</button>
            <button
              id='button2'
              data-attribute="ga-event"
              data-ga-action="back-to-report"
              data-ga-category="testing"
              data-ga-label="site-interaction"
            >2</button>
        </div>
        <form name="create_account" method="post" novalidate="">
        <fieldset class="govuk-fieldset">
            <legend class="govuk-fieldset__legend govuk-fieldset__legend--xl">
                <h1 class="govuk-fieldset__heading">Create an account</h1>
            </legend>
            <div class="govuk-form-group govuk-form-group--error">
                <label class="govuk-label" for="email">
                    Enter your email address
                </label>
                <p class="govuk-error-message">
                    <span class="govuk-visually-hidden">Error:</span> Enter an email address in the correct format, like name@example.com
                </p>
                <input class="govuk-input" id="email" name="email" type="email" value="" inputmode="email" spellcheck="false" autocomplete="email">
            </div>
            <div class="govuk-form-group form-group--error">
                <label class="govuk-label" for="email">
                    Confirm your email address
                </label>
                <p class="govuk-error-message">
                    <span class="govuk-visually-hidden">Error:</span> Your email address does not match
                </p>
                <input class="govuk-input" id="email" name="email" type="email" value="" inputmode="email" spellcheck="false" autocomplete="email">
            </div>
            <div class="govuk-form-group govuk-form-group--error">
                <label class="govuk-label" for="show_hide_password">
                    Create a password
                </label>
                <p class="govuk-error-message">
                    <span class="govuk-visually-hidden">Error:</span> Password must be 8 characters or more
                </p>
                <p class="govuk-error-message">
                    <span class="govuk-visually-hidden">Error:</span> Password must include a number
                </p>
                <p class="govuk-error-message">
                    <span class="govuk-visually-hidden">Error:</span> Password must include a capital letter
                </p>
                <input class="govuk-input govuk-input moj-password-reveal__input govuk-input--width-20" id="show_hide_password" name="show_hide_password" type="password" value="">
                <button class="govuk-button govuk-button--secondary moj-password-reveal__button" data-module="govuk-button" type="button" data-showpassword="Show" data-hidepassword="Hide">Show</button>
            </div>
            <button data-prevent-double-click="true" type="submit" class="govuk-button">Create account</button>
        </fieldset>
    </form>
    `
}

beforeAll(() => {
  setDocumentBody()
  GoogleAnalyticsEvents.init()
})

describe('googleAnalyticsEvents', () => {
  const expectedEmailErrorMessage = 'Enter an email address in the correct format, like name@example.com'
  const expectedConfirmEmailErrorMessage = 'Your email address does not match'
  const expectedPasswordErrorMessageOne = 'Password must be 8 characters or more'
  const expectedPasswordErrorMessageTwo = 'Password must include a number'
  const expectedPasswordErrorMessageThree = 'Password must include a capital letter'

  describe('initFormValidationErrors', () => {
    it('send form validation errors to google analytics', () => {
      GoogleAnalyticsEvents.initFormValidationErrors()

      expect(window.gtag).toHaveBeenNthCalledWith(
        1,
        'event',
        'Enter your email address',
        { event_category: 'Form errors', event_label: `#email - ${expectedEmailErrorMessage}` }
      )

      expect(window.gtag).toHaveBeenNthCalledWith(
        2,
        'event',
        'Create a password',
        { event_category: 'Form errors', event_label: `#show_hide_password - ${expectedPasswordErrorMessageOne}` }
      )

      expect(window.gtag).toHaveBeenNthCalledWith(
        3,
        'event',
        'Create a password',
        { event_category: 'Form errors', event_label: `#show_hide_password - ${expectedPasswordErrorMessageTwo}` }
      )

      expect(window.gtag).toHaveBeenNthCalledWith(
        4,
        'event',
        'Create a password',
        { event_category: 'Form errors', event_label: `#show_hide_password - ${expectedPasswordErrorMessageThree}` }
      )
    })
  })

  describe('extractFormErrorEventInfo', () => {
    it('returns an array of error event objects', () => {
      const actualEventInfos = GoogleAnalyticsEvents.extractFormErrorEventInfo('govuk-form-group--error', 'govuk-error-message')

      const exepctedEventInfos = [
        {
          action: 'Enter your email address',
          params: { event_category: 'Form errors', event_label: `#email - ${expectedEmailErrorMessage}` }
        },
        {
          action: 'Create a password',
          params: {
            event_category: 'Form errors',
            event_label: `#show_hide_password - ${expectedPasswordErrorMessageOne}`
          }
        },
        {
          action: 'Create a password',
          params: {
            event_category: 'Form errors',
            event_label: `#show_hide_password - ${expectedPasswordErrorMessageTwo}`
          }
        },
        {
          action: 'Create a password',
          params: {
            event_category: 'Form errors',
            event_label: `#show_hide_password - ${expectedPasswordErrorMessageThree}`
          }
        }
      ]

      expect(actualEventInfos).toEqual(exepctedEventInfos)

      const moreActualEventInfos = GoogleAnalyticsEvents.extractFormErrorEventInfo('form-group--error', 'govuk-error-message')

      const moreExepctedEventInfos = [
        {
          action: 'Confirm your email address',
          params: { event_category: 'Form errors', event_label: `#email - ${expectedConfirmEmailErrorMessage}` }
        }
      ]

      expect(moreActualEventInfos).toEqual(moreExepctedEventInfos)
    })
  })

  describe('extractEventInfo', () => {
    it('extracts event action, event_category and event_label from ga-event element', () => {
      const button1 = document.getElementById('button1')

      const actualEventInfo = GoogleAnalyticsEvents.extractEventInfo(button1)

      const expectedEventInfo = {
        action: 'form-submitted',
        event_params: { event_category: 'user-journeys', event_label: 'button-clicks' }
      }

      expect(actualEventInfo).toEqual(expectedEventInfo)
    })
  })

  describe('clicking button', () => {
    describe('when gtag is loaded', () => {
      it('dispatches gtag event', () => {
        document.getElementById('button1').click()
        document.getElementById('button2').click()

        expect(window.gtag).toHaveBeenCalledWith(
          'event',
          'form-submitted',
          { event_category: 'user-journeys', event_label: 'button-clicks' }
        )

        expect(window.gtag).toHaveBeenCalledWith(
          'event',
          'back-to-report',
          { event_category: 'testing', event_label: 'site-interaction' })
      })
    })

    describe('when gtag is not loaded', () => {
      it('does not dispatch gtag event', () => {
        window.globals.gtag = null

        document.getElementById('button1').click()
        document.getElementById('button2').click()

        expect(window.gtag).not.toHaveBeenCalled()
      })
    })
  })
})
