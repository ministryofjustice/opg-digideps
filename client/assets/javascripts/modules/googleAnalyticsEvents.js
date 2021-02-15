class GoogleAnalyticsEvents {
  static eventInfo = [];

  static init (eventName) {
    // - Find element with data-attribute = ga-event
    // - Get values of data-label, data-category, data-action, data-value
    // - Write macro to create element with the data attributes included - hard code category based on macroname?
    // - Add onclickevent with relevant ga code to element:
    //
    // gtag('event', <action>, {
    //     'event_category': <category>,
    //     'event_label': <label>,
    //     'value': <value>
    //     });

    const elements = document.querySelectorAll('button[data-attribute="ga-event"]')

    elements.forEach(element => {
      element.addEventListener(eventName, (e) => { this.extractEventInfo(e) })
    })
  }

  static extractEventInfo (eventElement) {
    this.eventInfo.push({
        "action": eventElement.dataset.action,
        "event_category": eventElement.dataset.category,
        "event_label": eventElement.dataset.label
    })
  }

  static sendEvent(gaEvent) {
    gtag('event', gaEvent.action, {
      "event_category": gaEvent.event_category,
      "event_label": gaEvent.event_label
    })
  }
}

export default GoogleAnalyticsEvents
