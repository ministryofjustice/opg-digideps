class GoogleAnalyticsEvents {
  static init () {
    const elements = document.querySelectorAll('button[data-attribute="ga-event"]')

    elements.forEach(element => {
      element.addEventListener('click', (e) => { this.sendEvent(e) })
    })
  }

  static extractEventInfo (eventElement) {
    return {
      action: eventElement.dataset.action,
      event_params:
        {
          event_category: eventElement.dataset.category,
          event_label: eventElement.dataset.label
        }
    }
  }

  static sendEvent (event) {
    const eventElement = event.target
    const eventInfo = this.extractEventInfo(eventElement)

    global.gtag('event', eventInfo.action, eventInfo.event_params)
  }
}

export { GoogleAnalyticsEvents }
