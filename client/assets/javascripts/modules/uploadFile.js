/* globals $ */
const UPLOAD_LIMIT = 15

module.exports = function (containerSelector) {
  // Show in progress message
  $(containerSelector).on('click', function () {
    const fileName = $('#report_document_upload_files').val()
    if (fileName) {
      $('#upload-progress').removeClass('hidden')
    }
  })

  // Show an error if file is over 15mb
  $('#upload_form').on('submit', function (e) {
    e.preventDefault()
    const fileElement = $('#report_document_upload_files')
    const actionUrl = $(this).attr('action')

    // check whether browser fully supports all File API
    if (
      window.File &&
      window.FileReader &&
      window.FileList &&
      window.Blob &&
      fileElement[0].files.length > 0
    ) {
      const fsize = fileElement[0].files[0].size
      if (fsize > UPLOAD_LIMIT * 1024 * 1024) {
        window.location = actionUrl + '?error=tooBig'
        return
      }
    }

    this.submit()
  })
}
