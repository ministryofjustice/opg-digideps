class GoogleAnalyticsEvents {
  static eventInfo = [];

  static init (eventName) {
    const elements = document.querySelectorAll('button[data-attribute="ga-event"]')

    elements.forEach(element => {
      element.addEventListener(eventName, (e) => { this.extractEventInfo(e) })
    })
  }

  static extractEventInfo (eventElement) {
    this.eventInfo.push({
      action: eventElement.dataset.action,
      event_params: {
        event_category: eventElement.dataset.category,
        event_label: eventElement.dataset.label
      }
    })
  }

  static sendEvent (gaEvent) {
    global.gtag('event', gaEvent.action, gaEvent.event_params)
  }
}

export default GoogleAnalyticsEvents
