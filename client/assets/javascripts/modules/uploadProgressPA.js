/* globals $, FormData */
const uploadProgressPA = function (element) {
  const $form = $(element)
  const $progress = $form.find('progress')
  const $button = $form.find('.govuk-button')
  const $uploadExplanation = $form.find('[data-js="upload-explanation"]')
  const $uploadError = $form.find('[data-js="upload-error"]')

  $button.on('click', (event) => {
    event.preventDefault()

    let redirectUrl = window.location.href
    const submitUrl = $form.attr('action') || window.location.href
    let isComplete = false

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
          const lines = e.currentTarget.response.split('\n')

          lines.forEach((line) => {
            const log = line.split(' ')
            const command = log.shift()

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
