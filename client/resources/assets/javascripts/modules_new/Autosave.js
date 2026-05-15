/**
 * Component to enable autosaves on a form. This is intended for forms
 * with a "save progress" button already in place (i.e. the form provides a
 * way to save partial data). This component converts the save button to
 * send an Ajax request when clicked; if an autosave request is in flight,
 * the "save progress" button is disabled.
 */
const Autosave = {
  init: function (document) {
    const autosaveForms = document.querySelectorAll('form.js-autosave')

    autosaveForms.forEach(autosaveForm => {
      const saveButton = autosaveForm.querySelector('button.js-autosave-save-progress-button')

      // Form elements which, when they change, don't trigger an autosave.
      //
      // This is necessary for forms which have a separate submit button for
      // some fields, e.g. the "Save further information" button on the checklist page.
      //
      // Note that these elements are still included if the "save progress"
      // button is explicitly pressed (preserving current behaviour on
      // the checklist page, for example).
      const ignoredElements = autosaveForm.querySelectorAll('.js-autosave-ignore')

      const handler = this.makeHandler(autosaveForm, saveButton, ignoredElements)

      saveButton.addEventListener('click', handler)
      autosaveForm.addEventListener('change', handler)
    })
  },

  makeHandler: function (autosaveForm, saveProgressButton, ignoredElements) {
    return async (e) => {
      let ignoredElement = false

      // if the event is a change event and came from an ignored element,
      // don't do anything
      if (e.type === 'change') {
        ignoredElements.forEach((el) => {
          if (el === e.target) {
            ignoredElement = true
          }
        })
      }

      if (!ignoredElement) {
        e.preventDefault()
        saveProgressButton.disabled = true

        const formData = new FormData(autosaveForm)

        // the name of the "save progress" button has to be part of the payload
        // so that the controller can route the request correctly
        formData.set(saveProgressButton.name, saveProgressButton.value)

        try {
          await fetch(autosaveForm.action, {
            method: 'POST',
            body: formData
          })
        } finally {
          saveProgressButton.disabled = false
        }
      }
    }
  }
}

export default Autosave
