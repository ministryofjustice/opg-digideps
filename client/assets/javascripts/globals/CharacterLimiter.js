const CharacterLimiter = {
  init: function () {
    const characterLimiters = document.querySelectorAll('[class*="js-limit-chars-"]')

    if (characterLimiters.length > 0) {
      characterLimiters.forEach((element) => {
        element.addEventListener('keyup', this.limiterFunction)
        element.addEventListener('input', this.limiterFunction)
        element.addEventListener('paste', this.limiterFunction)
        element.addEventListener('change', this.limiterFunction)
      })
    }
  },

  limiterFunction: function (event) {
    const elClass = event.target.getAttribute('class')
    const charsLimit = parseInt(
      elClass.substr(elClass.indexOf('limit-chars-') + 12, 1)
    )
    const chars = event.target.value.length

    if (chars <= charsLimit) {
      return true
    } else {
      let str = event.target.value
      str = str.substring(0, str.length - 1)
      event.target.value = str
    }
  }

}

export default CharacterLimiter
