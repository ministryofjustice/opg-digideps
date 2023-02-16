module.exports = {
  init: function () {
    function toggleHidden (elem, toggleElement) {
      const expanded = elem.checked ? 'true' : 'false'
      const hidden = elem.checked ? 'false' : 'true'
      elem.setAttribute('aria-expanded', expanded)
      toggleElement?.setAttribute('aria-hidden', hidden)
      toggleElement?.classList.toggle('js-hidden', !elem.checked)
    }

    // dom setup

    document.querySelectorAll('[data-target]')?.forEach(function (el) {
      const targetElementID = el.dataset.target
      const cb = el.querySelector('input')
      const toggleElement = document.getElementById(targetElementID)
      cb.setAttribute('aria-controls', targetElementID)

      toggleHidden(cb, toggleElement)
    })

    // add events

    document.addEventListener('click', function (event) {
      let elem = event.target

      if (elem.matches('input[type="radio"]')) {
        const parent = elem.closest('[data-module="govuk-radios"]')
        const name = elem.getAttribute('name')
        elem = parent?.querySelector(`input[type="radio"][name="${name}"][aria-controls]`)
      }

      const controls = elem.getAttribute('aria-controls')
      const toggleElement = document.getElementById(controls)

      if (controls && toggleElement) {
        if (elem.matches('input[type="checkbox"],input[type="radio"]')) {
          toggleHidden(elem, toggleElement)
        }
      }
    })
  }
}
