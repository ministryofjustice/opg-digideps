/* globals $ */
// Auto size textarea
// Use the class name of .js-auto-size on the textarea
// Note that associated styles live under _.forms.scss

module.exports = function (containerSelector) {
  const textArea = $(containerSelector).find("[class*='js-auto-size'] textarea")

  textArea.on('keyup input paste change', function (event) {
    const $this = $(this)
    const initialHeight = $this.height()

    $this
      .height(initialHeight - 20)
      .height($this[0].scrollHeight + 20)
  }).trigger('keyup')
}
