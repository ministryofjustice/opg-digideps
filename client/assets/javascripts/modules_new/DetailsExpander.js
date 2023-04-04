const DetailsExpander = {
  init: function (document) {
    const container = document.querySelectorAll('.js-details-expander')

    container.forEach((userItem) => {
      const inputBox = userItem.querySelector('input[type="text"]')

      inputBox.addEventListener('input', this.expandDetails)
      inputBox.addEventListener('paste', this.expandDetails)
      inputBox.addEventListener('change', this.expandDetails)
    })
  },

  expandDetails: function (event) {
    const container = document.querySelectorAll('.js-details-expander')

    container.forEach((userItem) => {
      const textareaGroup = userItem.querySelector('.js-details-expandable')
      if (textareaGroup) {
        if (userItem.contains(event.target)) {
          const value = parseFloat(event.target.value.replace(/,/g, ''))
          if (!isNaN(value) && value !== 0) {
            textareaGroup.classList.remove('js-hidden')
          } else {
            textareaGroup.classList.add('js-hidden')
          }
        }
      }
    })
  }
}

export default DetailsExpander
