const DetailsExpander = {
  init: function (document) {
    const expanders = document.querySelectorAll('.js-details-expander')

    expanders.forEach(expander => {
      const textInput = expander.querySelector('input[type="text"]')
      textInput.addEventListener('input', this.expandDetails)
      textInput.addEventListener('paste', this.expandDetails)
      textInput.addEventListener('change', this.expandDetails)
    })
  },

  expandDetails: function (event) {
    const expanders = document.querySelectorAll('.js-details-expander')

    expanders.forEach(expander => {
      const expandableElt = expander.querySelector('.js-details-expandable')
      if (expandableElt && expander.contains(event.target)) {
        const numericValue = parseFloat(event.target.value.replace(/,/g, ''))
        const expandIfZero = event.target.hasAttribute('data-expand-if-zero')

        const shouldExpand = !isNaN(numericValue) && (
          (expandIfZero && numericValue === 0.0) || (!expandIfZero && numericValue > 0.0)
        )

        expandableElt.classList.toggle('js-hidden', !shouldExpand)
      }
    })
  }
}

export default DetailsExpander
