const DetailsExpander = {
  init: function (nodes) {
    // TODO refactor to event delegation
    const container = nodes.querySelector('.js-details-expander')

    const expandDetails = function (event) {
      const textareaGroup = container.querySelector('.js-details-expandable')
      if (textareaGroup) {
        const value = parseFloat(event.target.value.replace(/,/g, ''))
        if (!isNaN(value) && value !== 0) {
          textareaGroup.classList.remove('js-hidden')
        } else {
          textareaGroup.classList.add('js-hidden')
        }
      }
    }

    if (container) {
      const inputBox = container.querySelector('input[type="text"]')

      inputBox.addEventListener('input', expandDetails)
      inputBox.addEventListener('paste', expandDetails)
      inputBox.addEventListener('change', expandDetails)
    }
  }
}

export default DetailsExpander
