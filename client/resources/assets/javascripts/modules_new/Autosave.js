/**
 * Component to enable autosaves on a form. This is intended for forms
 * with a "save progress" button already in place (i.e. the form provides a
 * way to save partial data). This component converts the save button to
 * send an Ajax request when clicked; if an autosave request is in flight,
 * the "save progress" button is disabled.
 */
const Autosave = {
  fetchFunction: null,
  autosaveForms: [],
  isAutosaving: false,
  window: null,

  init: function (window, autosavePeriodSecs, fetchFunction) {
    this.window = window
    this.isAutosaving = false
    this.autosaveForms = window.document.querySelectorAll('form.js-autosave')

    if (fetchFunction === undefined) {
      fetchFunction = window.fetch.bind(window)
    }
    this.fetchFunction = fetchFunction

    this.autosaveForms.forEach((autosaveForm) => {
      const saveProgressButton = autosaveForm.querySelector('button.js-autosave-save-progress-button')

      // Form elements which, when they change, don't trigger an autosave.
      //
      // This is necessary for forms which have a separate submit button for
      // some fields, e.g. the "Save further information" button on the checklist page.
      //
      // Note that these elements are still included if the "save progress"
      // button is explicitly pressed (preserving current behaviour on
      // the checklist page, for example).
      const ignoredElements = Array.from(autosaveForm.querySelectorAll('.js-autosave-ignore'))
      const ignoredElementNames = ignoredElements.map((el) => el.name)

      // this handler will save all fields (current behaviour)
      const clickHandler = this.makeHandler(autosaveForm, saveProgressButton, [], [])
      saveProgressButton.addEventListener('click', clickHandler)

      // this handler will not save ignored fields
      const nonIgnoredHandler = this.makeHandler(autosaveForm, saveProgressButton, ignoredElements, ignoredElementNames)
      autosaveForm.addEventListener('change', nonIgnoredHandler)

      // periodically save every autosavePeriodSecs seconds; this will not save
      // ignored fields
      const autosaveIntervalId = setInterval(() => {
        return this.autosave(saveProgressButton, autosaveForm, ignoredElementNames)
      }, autosavePeriodSecs * 1000)

      autosaveForm.setAttribute('autosave-interval-id', autosaveIntervalId)
    })

    return this
  },

  makeHandler: function (autosaveForm, saveProgressButton, ignoredElements, ignoredElementNames) {
    return async (e) => {
      // if the event is a change event and came from an ignored element,
      // don't do anything
      if (e.type === 'change' && ignoredElements.some((el) => el === e.target)) {
        return
      }

      e.preventDefault()

      return this.autosave(saveProgressButton, autosaveForm, ignoredElementNames)
    }
  },

  autosave: async function (saveProgressButton, autosaveForm, ignoredElementNames) {
    if (this.isAutosaving) {
      return false
    }

    this.isAutosaving = true
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

    const doneCallback = function (response) {
      this.isAutosaving = false
      saveProgressButton.disabled = false

      // check whether the response was a redirect to the login page;
      // if so, the autosave failed, and the user needs to sign in again:
      // redirect them to the login URL in the response
      if (response && response.redirected && response.url.includes('/login')) {
        this.window.location.href = response.url
      }
    }.bind(this)

    await this.fetchFunction(autosaveForm.action, {
      method: 'POST',
      body: formData
    })
      .then(doneCallback)
      .catch(() => {
        this.isAutosaving = false
        saveProgressButton.disabled = false
      })

    return true
  }
}

export default Autosave
