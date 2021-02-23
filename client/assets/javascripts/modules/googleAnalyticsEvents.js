class GoogleAnalyticsEvents {
  static init () {
    document.addEventListener('click', (e) => {
      if (e.target && e.target.matches('button[data-attribute="ga-event"]')) {
        this.sendEvent(e)
      }
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
    if (typeof window.globals.gtag === 'function') {
      const eventElement = event.target
      const eventInfo = this.extractEventInfo(eventElement)

      window.globals.gtag('event', eventInfo.action, eventInfo.event_params)
    }
  }
}

export { GoogleAnalyticsEvents }
