// only works for yes/no radio buttons;
// see client/app/templates/Report/Lifestyle/step.html.twig for an example
module.exports = {
  init: function(document) {
    function showElement(elt) {
      elt.setAttribute('aria-expanded', 'true')
      elt.setAttribute('aria-hidden', 'false')
      elt.classList.remove('js-hidden')
    }

    function hideElement(elt) {
      elt.setAttribute('aria-expanded', 'false')
      elt.setAttribute('aria-hidden', 'true')
      elt.classList.add('js-hidden')
    }

    // the 'change' event only fires when a radio button is checked, not
    // when it is unchecked
    document.addEventListener('change', function (event) {
      let elem = event.target

      if (elem || elem.matches('input[type="radio"][data-multitoggle-on]')) {
        const toggledElementsContainer = elem.closest('[data-role="multitoggle-group"]')

        if (elem.checked) {
          let onElt = toggledElementsContainer.querySelector('#' + elem.getAttribute('data-multitoggle-on'))
          let offElt = toggledElementsContainer.querySelector('#' + elem.getAttribute('data-multitoggle-off'))

          showElement(onElt)
          hideElement(offElt)
        }
      }
    })
  }
}
