/* globals $ */
module.exports = function (containerSelector) {
  $(containerSelector).on('click', function (e) {
    e.preventDefault()
    var link = $(this)
    $.get(link.attr('href'), function (data) {
      link.replaceWith(data)
    }, 'html')
  })
}
