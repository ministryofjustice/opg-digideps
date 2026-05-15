/**
 * Component to enable autosaves on a form. This is intended for forms
 * with a "save progress" button already in place (i.e. the form provides a
 * way to save partial data). This component converts the save button to
 * send an Ajax request when clicked; if an autosave request is in flight,
 * the "save progress" button is disabled.
 */
const Autosave = {
  init: function (document) {
    // enable referencing Autosave prototype in other functions
    const that = this

    const autosaveForms = document.querySelectorAll('form.js-autosave')

    autosaveForms.forEach(autosaveForm => {
      const saveProgressButton = autosaveForm.querySelector('button.js-autosave-save-progress-button')

      // Form elements which, when they change, don't trigger an autosave.
      //
      // This is necessary for forms which have a separate submit button for
      // some fields, e.g. the "Save further information" button on the checklist page.
      //
      // Note that these elements are still included if the "save progress"
      // button is explicitly pressed (preserving current behaviour on
      // the checklist page, for example).
      const ignoredElements = autosaveForm.querySelectorAll('.js-autosave-ignore')
      const ignoredElementNames = Array.from(ignoredElements).map(el => el.name)

      // this handler will save all fields (current behaviour)
      const clickHandler = this.makeHandler(autosaveForm, saveProgressButton, [], [])
      saveProgressButton.addEventListener('click', clickHandler)

      // this handler will not save ignored fields
      const autosaveHandler = this.makeHandler(autosaveForm, saveProgressButton, ignoredElements, ignoredElementNames)
      autosaveForm.addEventListener('change', autosaveHandler)

      // periodically save every 30s; this will not save ignored fields
      setInterval(() => {
        that.autosave(saveProgressButton, autosaveForm, ignoredElementNames)
      }, 30000)
    })
  },

  makeHandler: function (autosaveForm, saveProgressButton, ignoredElements, ignoredElementNames) {
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
        await this.autosave(saveProgressButton, autosaveForm, ignoredElementNames)
      }
    }
  },

  autosave: async function (saveProgressButton, autosaveForm, ignoredElementNames) {
    saveProgressButton.disabled = true

    const formData = new FormData(autosaveForm)

    // the name of the "save progress" button has to be part of the payload
    // so that the controller can route the request correctly
    formData.set(saveProgressButton.name, saveProgressButton.value)

    // for safety's sake, remove the values of ignored elements from
    // the payload (for the checklist, this prevents a new "further information"
    // entry being created by timed autosaves)
    ignoredElementNames.forEach((name) => {
      formData.delete(name)
    })

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

export default Autosave
