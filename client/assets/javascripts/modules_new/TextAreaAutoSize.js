const TextAreaAutoSize = {
  init: function (formArea) {
    formArea.addEventListener('input', this.resizeTextArea)
    formArea.addEventListener('keyup', this.resizeTextArea)
    formArea.addEventListener('paste', this.resizeTextArea)
    formArea.addEventListener('change', this.resizeTextArea)
  },

  resizeTextArea: function (event) {
    if (event.target.tagName === 'TEXTAREA' && event.target.classList.contains('js-auto-size')) {
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
