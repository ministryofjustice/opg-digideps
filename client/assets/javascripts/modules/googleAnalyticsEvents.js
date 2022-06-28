class GoogleAnalyticsEvents {
  static init () {
    document.addEventListener('click', (e) => {
      if (e.target && e.target.matches('button[data-attribute="ga-event"]')) {
        this.sendEvent(e)
      }
    })
  }

  static initFormValidationErrors () {

  }

  static extractFormErrorEventInfo (formGroup, errorElement) {
    return [{"action": "Enter your email address", "params": {"event_category": "Form errors", "event_label": "#email - Enter an email address in the correct format, like name@example.com"}}, {"action": "Create a password", "params": {"event_category": "Form errors", "event_label": "#show_hide_password - Password must be 8 characters or more"}}, {"action": "Create a password", "params": {"event_category": "Form errors", "event_label": "#show_hide_password - Password must include a number"}}, {"action": "Create a password", "params": {"event_category": "Form errors", "event_label": "#show_hide_password - Password must include a capital letter"}}]
  }

  static extractEventInfo (eventElement) {
    return {
      action: eventElement.dataset.gaAction,
      event_params:
        {
          event_category: eventElement.dataset.gaCategory,
          event_label: eventElement.dataset.gaLabel
        }
    }
  }

  static sendEvent (event) {
    if (typeof window.globals.gtag === 'function') {
      const eventElement = event.target
      const eventInfo = this.extractEventInfo(eventElement)

      window.globals.gtag('event', eventInfo.action, eventInfo.event_params)
    }
  }
}

export { GoogleAnalyticsEvents }
