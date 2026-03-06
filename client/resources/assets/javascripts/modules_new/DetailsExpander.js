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
        const cleanedValue = event.target.value.replace(/,/g, '')
        const isNonNumeric = event.target.hasAttribute('data-non-numeric')
        const numericValue = parseFloat(cleanedValue)

        const shouldExpand = (isNonNumeric && cleanedValue !== '')
          || (!isNaN(numericValue) && numericValue !== 0)

        expandableElt.classList.toggle('js-hidden', !shouldExpand)
      }
    })
  }
}

export default DetailsExpander
