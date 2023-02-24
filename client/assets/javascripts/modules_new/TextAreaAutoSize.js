const TextAreaAutoSize = {
  init: function (element) {
    element.addEventListener('input', this.resizeTextArea)
    element.addEventListener('keyup', this.resizeTextArea)
    element.addEventListener('paste', this.resizeTextArea)
    element.addEventListener('change', this.resizeTextArea)
  },

  resizeTextArea: function (event) {
    if (event.target.closest('.js-auto-size')) {
      const textArea = event.target
      const scrollHeight = (textArea.scrollHeight) || 120
      const height = textArea.clientHeight

      if (scrollHeight > height) {
        textArea.style.height = (scrollHeight + 10) + 'px'
      }
    }
  }
}

export default TextAreaAutoSize
