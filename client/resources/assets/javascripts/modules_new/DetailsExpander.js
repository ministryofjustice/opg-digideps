const DetailsExpander = {
  init: function (document) {
    const expanders = document.querySelectorAll('.js-details-expander')

    expanders.forEach(expander => {
      const triggerElement = expander.querySelector('input[type="text"]')
      const expandableElt = expander.querySelector('.js-details-expandable')

      const handler = this.makeHandler(triggerElement, expandableElt)

      triggerElement.addEventListener('input', handler)
      triggerElement.addEventListener('paste', handler)
      triggerElement.addEventListener('change', handler)

      // set starting visibility of expandable element
      handler()
    })
  },

  makeHandler: function (triggerElement, expandableElement) {
    return function () {
      const numericValue = parseFloat(triggerElement.value.replace(/,/g, ''))

      // if this attribute is present, the expandable element is shown only
      // when the value of the expander text input is zero; otherwise, the
      // expandable element is shown when the text input is *not* zero (default)
      const expandIfZero = triggerElement.hasAttribute('data-expand-if-zero')

      const shouldExpand = !isNaN(numericValue) && (
        (expandIfZero && numericValue === 0.0) || (!expandIfZero && numericValue > 0.0)
      )

      // if the expandable element contains an aria-live element, update
      // its content so that users of assistive technologies are aware that
      // additional information is being requested
      const ariaLiveElement = expandableElement.querySelector('[aria-live]')
      if (ariaLiveElement) {
        ariaLiveElement.textContent = shouldExpand
          ? 'Additional information is required'
          : 'Additional information is not required'
      }

      expandableElement.classList.toggle('js-hidden', !shouldExpand)
      expandableElement.setAttribute('aria-hidden', !shouldExpand)
      expandableElement.setAttribute('aria-expanded', shouldExpand)
    }
  }
}

export default DetailsExpander
