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
      saveButton.addEventListener('click', this.makeHandler(autosaveForm, saveButton))
    })
  },

  makeHandler: function (autosaveForm, saveProgressButton) {
    return async (e) => {
      e.preventDefault()
      saveProgressButton.disabled = true

      const formData = new FormData(autosaveForm)

      // the name of the button has to be part of the payload
      // so that the controller can route it correctly
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

export default Autosave
