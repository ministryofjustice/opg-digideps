/* globals $ */
// CHARACTER LIMITER
// Use the class name of .js-limit-chars-x on the input
// The 'x' will be the character limit

module.exports = function (containerSelector) {
  const limitElement = $(containerSelector).find("[class*='js-limit-chars-']")

  limitElement
    .on('keyup input paste change', function (event) {
      const $this = $(event.target)
      // Get the classes
      const elClass = $this.attr('class')
      // Get the limiter value (the 'x')
      const charsLimit = parseInt(
        elClass.substr(elClass.indexOf('limit-chars-') + 12, 1)
      )
      // The amount of chars in the input
      const chars = $this.val().length

      if (chars <= charsLimit) {
        return true
      } else {
        let str = $this.val()
        str = str.substring(0, str.length - 1)
        $this.val(str)
      }
    })
    .trigger('keyup')
}
