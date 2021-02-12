class GoogleAnalyticsEvents {
  static init () {
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

    const elements = document.querySelectorAll('button[data-attribute="gae"]')

    elements.forEach(element => {
      element.addEventListener('userStartsURSection', () => {})
    })
  }
}

export default GoogleAnalyticsEvents
