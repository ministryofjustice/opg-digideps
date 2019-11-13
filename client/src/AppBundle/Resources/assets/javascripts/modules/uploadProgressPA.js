/* globals $, FormData */
var uploadProgressPA = function (element) {
  var $form = $(element)
  var $progress = $form.find('progress')
  var $button = $form.find('.govuk-button')

  $button.on('click', (event) => {
    event.preventDefault()

    var redirectUrl = window.location.href
    var submitUrl = $form.attr('action') || window.location.href

    $progress.removeClass('hidden')
    $button.prop('disabled', true)

    $.ajax({
      url: submitUrl + '?ajax=1',
      type: 'POST',
      data: new FormData($form[0]),
      processData: false,
      contentType: false,
      xhrFields: {
        onprogress: function (e) {
          const lines = e.currentTarget.response.split('\n')

          lines.forEach(line => {
            const log = line.split(' ')
            const command = log.shift()

            if (command === 'PROG') {
              $progress.val(parseInt(log[0]) / parseInt(log[1]))
            } else if (command === 'REDIR') {
              redirectUrl = log[0]
            }
          })
        }
      }
    }).done(function () {
      $progress.val(1)
      window.location.href = redirectUrl
    })
  })
}

module.exports = uploadProgressPA
