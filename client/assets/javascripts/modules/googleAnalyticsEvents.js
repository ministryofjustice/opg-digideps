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

  static extractFormErrorEventInfo (formGroupClass, errorElementClass) {
    const errorEventInfos = []

    const formGroups = document.getElementsByClassName(formGroupClass)

    for (const formGroup of formGroups) {
      const labelElement = formGroup.getElementsByTagName('label')[0]
      const label = labelElement.textContent.trim()
      const inputId = labelElement.getAttribute('for')
      const errorMessages = formGroup.getElementsByClassName(errorElementClass)

      for (const errorMessage of errorMessages) {
        const event = {}
        const params = {}
        event.action = label
        const messageContent = errorMessage.textContent.replace("Error:", "").trim()

        params.event_category = 'Form errors'
        params.event_label = `#${inputId} - ${messageContent}`

        event.params = params

        errorEventInfos.push(event)
      }
    }

    return errorEventInfos
  }

  static extractEventInfo (eventElement) {
    return {
      action: eventElement.dataset.gaAction, event_params: {
        event_category: eventElement.dataset.gaCategory, event_label: eventElement.dataset.gaLabel
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
