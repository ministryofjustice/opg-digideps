const TextAreaAutoSize = {
  init: function (formArea) {
    const textAreas = formArea.querySelectorAll('[class*="js-auto-size"] textarea')

    textAreas.forEach(textArea => {
      textArea.addEventListener('input', this.resizeTextArea)
      textArea.addEventListener('keyup', this.resizeTextArea)
      textArea.addEventListener('paste', this.resizeTextArea)
      textArea.addEventListener('change', this.resizeTextArea)
    })
  },

  resizeTextArea: function (event) {
    const textArea = event.target
    const initialHeight = textArea.style.height

    textArea.style.height = initialHeight - '20px'
    textArea.style.height = textArea.scrollHeight + '20px'
  }
}

export default TextAreaAutoSize
