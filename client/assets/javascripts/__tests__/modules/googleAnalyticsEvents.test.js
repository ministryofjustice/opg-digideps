import { GoogleAnalyticsEvents } from '../../modules/googleAnalyticsEvents'
import { describe, it, jest } from '@jest/globals'

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

describe('googleAnalyticsEvents', () => {
  describe('init', () => {
    it('attaches event listeners to elements with data-attributes=ga-event', () => {
      setDocumentBody()

      const buttons = document.querySelectorAll('button[data-attribute="ga-event"]')
      const spies = []

      buttons.forEach(button => {
        spies.push(jest.spyOn(button, 'addEventListener'))
      })

      GoogleAnalyticsEvents.init()

      spies.forEach(spy => {
        expect(spy).toHaveBeenCalledTimes(1)
        expect(spy).toHaveBeenCalledWith('click', expect.any(Function))
      })
    })
  })

  describe('extractEventInfo', () => {
    it('extracts event action, event_category and event_label from ga-event element', () => {
      setDocumentBody()

      GoogleAnalyticsEvents.init('userStartsURSection')

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
      jest.spyOn(global, 'gtag').mockReturnValueOnce(true)

      setDocumentBody()
      GoogleAnalyticsEvents.init()

      simulateClick(document.getElementById('button1'))

      expect(global.gtag).toHaveBeenCalledTimes(1)
      expect(global.gtag).toHaveBeenCalledWith('event', 'form-submitted', { event_category: 'user-journeys', event_label: 'button-clicks' })
    })
  })
})
