// only works for yes/no radio buttons;
// see client/app/templates/Report/Lifestyle/step.html.twig for an example
module.exports = {
  init: function (document) {
    function showElement (elt) {
      elt.setAttribute('aria-expanded', 'true')
      elt.setAttribute('aria-hidden', 'false')
      elt.classList.remove('js-hidden')
    }

    function hideElement (elt) {
      elt.setAttribute('aria-expanded', 'false')
      elt.setAttribute('aria-hidden', 'true')
      elt.classList.add('js-hidden')
    }

    function setTogglableState (elt) {
      const toggledElementsContainer = elt.closest('[data-role="multitoggle-group"]')

      const onElt = toggledElementsContainer.querySelector('#' + elt.getAttribute('data-multitoggle-on'))
      const offElt = toggledElementsContainer.querySelector('#' + elt.getAttribute('data-multitoggle-off'))

      showElement(onElt)
      hideElement(offElt)
    }

    // set initial state of togglable areas based on radio button checks
    window.addEventListener('load', function () {
      const selector = 'input[type="radio"][data-multitoggle-on],input[type="radio"][data-multitoggle-off]'
      document.querySelectorAll(selector).forEach(function (elt) {
        if (elt.checked) {
          setTogglableState(elt)
        }
      })
    })

    // event handler for change events on radio buttons after page load;
    // the 'change' event only fires when a radio button is checked, not
    // when it is unchecked
    document.addEventListener('change', function (event) {
      const elt = event.target

      if (elt && elt.matches('input[type="radio"][data-multitoggle-on]') && elt.checked) {
        setTogglableState(elt)
      }
    })
  }
}
