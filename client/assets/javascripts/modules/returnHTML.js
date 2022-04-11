/* globals $ */
module.exports = function (containerSelector) {
  $(containerSelector).on('click', function (e) {
    e.preventDefault()
    const link = $(this)
    $.get(
      link.attr('href'),
      function (data) {
        link.replaceWith(data)
      },
      'html'
    )
  })
}
