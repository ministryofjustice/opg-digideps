import GoogleAnalyticsEvents from '../../modules/googleAnalyticsEvents'

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

describe('googleAnalyticsEvents', () => {
  describe('init', () => {
    it('attaches event listeners to elements with data-attributes=ga-event', () => {
      setDocumentBody()

      const buttons = document.querySelectorAll('button[data-attribute="ga-event"]')
      const spies = []

      buttons.forEach(button => {
        spies.push(jest.spyOn(button, 'addEventListener'))
      })

      GoogleAnalyticsEvents.init('userStartsURSection')

      spies.forEach(spy => {
        expect(spy).toHaveBeenCalledTimes(1)
        expect(spy).toHaveBeenCalledWith('userStartsURSection', expect.any(Function))
      })
    })
  })

  describe('extractEventInfo', () => {
    it('extracts event action, event_category and event_label from ga-event element', () => {
      setDocumentBody()

      GoogleAnalyticsEvents.init('userStartsURSection')

      const button1 = document.getElementById('button1')
      const button2 = document.getElementById('button2')

      GoogleAnalyticsEvents.extractEventInfo(button1)
      GoogleAnalyticsEvents.extractEventInfo(button2)

      const expectedEventInfo = [
        { action: 'form-submitted', event_category: 'user-journeys', event_label: 'button-clicks' },
        { action: 'back-to-report', event_category: 'user-journeys', event_label: 'button-clicks' }
      ]

      expect(GoogleAnalyticsEvents.eventInfo).toEqual(expectedEventInfo)
    })
  })

  describe('sendEvent', () => {
    jest.spyOn(global, 'gtag').mockReturnValueOnce(true)

    const gaEvent = { action: 'form-submitted', event_category: 'user-journeys', event_label: 'button-clicks' }

    // Look at having an anon function and mocking calling gtag e.g. gtag = function(){}
    GoogleAnalyticsEvents.sendEvent(gaEvent)

    expect(global.gtag).toHaveBeenCalledTimes(1)
    expect(global.gtag).toHaveBeenCalledWith('event', 'form-submitted', { event_category: 'user-journeys', event_label: 'button-clicks' })
  })
})
