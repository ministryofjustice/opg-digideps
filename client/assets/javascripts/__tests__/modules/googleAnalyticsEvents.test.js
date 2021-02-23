import { GoogleAnalyticsEvents } from '../../modules/googleAnalyticsEvents'
import { beforeAll, describe, it, jest } from '@jest/globals'

const setDocumentBody = () => {
  document.body.innerHTML = `
        <div>
            <button
              id='button1'
              data-attribute="ga-event"
              data-action="form-submitted"
              data-category="user-journeys"
              data-label="button-clicks"
            >1</button>
            <button
              id='button2'
              data-attribute="ga-event"
              data-action="back-to-report"
              data-category="user-journeys"
              data-label="button-clicks"
            >2</button>
        </div>
    `
}

const simulateClick = (element) => {
  // Create our event (with options)
  const event = new global.MouseEvent('click', {
    bubbles: true,
    cancelable: true,
    view: window
  })

  element.dispatchEvent(event)
}

beforeAll(() => {
  setDocumentBody()
  GoogleAnalyticsEvents.init()
})

describe('googleAnalyticsEvents', () => {
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
    it('dispatches gtag event', () => {
      global.gtag = jest.fn()

      simulateClick(document.getElementById('button1'))
      simulateClick(document.getElementById('button2'))

      expect(global.gtag).toHaveBeenCalledTimes(2)
      expect(global.gtag).toHaveBeenCalledWith('event', 'form-submitted', { event_category: 'user-journeys', event_label: 'button-clicks' })
    })
  })
})
