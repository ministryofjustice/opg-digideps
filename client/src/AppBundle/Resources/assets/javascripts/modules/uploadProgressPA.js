/* globals $, FormData */
var uploadProgressPA = function (element) {
  var $form = $(element)
  var $progress = $form.find('progress')
  var $button = $form.find('.govuk-button')
  var $uploadExplanation = $form.find('[data-js="upload-explanation"]')
  var $uploadError = $form.find('[data-js="upload-error"]')

  $button.on('click', function (event) {
    event.preventDefault()

    var redirectUrl = window.location.href
    var submitUrl = $form.attr('action') || window.location.href
    var isComplete = false

    $progress.removeClass('hidden')
    $uploadExplanation.removeClass('hidden')
    $button.prop('disabled', true)

    $.ajax({
      url: submitUrl + '?ajax=1',
      type: 'POST',
      data: new FormData($form[0]),
      processData: false,
      contentType: false,
      xhrFields: {
        onprogress: function (e) {
          var lines = e.currentTarget.response.split('\n')

          lines.forEach(function (line) {
            var log = line.split(' ')
            var command = log.shift()

            if (command === 'PROG') {
              $progress.val(parseInt(log[0]) / parseInt(log[1]))
            } else if (command === 'REDIR') {
              redirectUrl = log[0]
            } else if (command === 'END') {
              isComplete = true
            }
          })
        }
      }
    }).done(function () {
      if (isComplete) {
        $progress.val(1)
        window.location.href = redirectUrl
      } else {
        $uploadError.removeClass('hidden')
        $uploadExplanation.addClass('hidden')
        $progress.addClass('hidden')
      }
    })
  })
}

module.exports = uploadProgressPA
