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
      const expandIfZero = triggerElement.hasAttribute('data-expand-if-zero')

      const shouldExpand = !isNaN(numericValue) && (
        (expandIfZero && numericValue === 0.0) || (!expandIfZero && numericValue > 0.0)
      )

      expandableElement.classList.toggle('js-hidden', !shouldExpand)
    }
  }
}

export default DetailsExpander
