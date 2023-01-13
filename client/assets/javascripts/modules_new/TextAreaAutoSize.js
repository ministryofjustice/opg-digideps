const TextAreaAutoSize = {
  init: function (formArea) {
    formArea.addEventListener('input', this.resizeTextArea)
    formArea.addEventListener('keyup', this.resizeTextArea)
    formArea.addEventListener('paste', this.resizeTextArea)
    formArea.addEventListener('change', this.resizeTextArea)
  },

  resizeTextArea: function (event) {
    if (event.target.tagName === 'TEXTAREA') {
      const textArea = event.target
      const initialHeight = textArea.style.height

      textArea.style.height = initialHeight - '20px'
      textArea.style.height = textArea.scrollHeight + '20px'
    }
  }
}

export default TextAreaAutoSize
