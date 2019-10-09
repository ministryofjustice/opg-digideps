/* globals $ */
module.exports = function (containerSelector) {
  // Show in progress message
  $(containerSelector).on('click', function () {
    var fileName = $('#report_document_upload_file').val()
    if (fileName) {
      $('#upload-progress').removeClass('hidden')
    }
  })

  // Show an error if file is over 15mb
  $('#upload_form').on('submit', function (e) {
    e.preventDefault()
    var fileElement = $('#report_document_upload_files')
    var actionUrl = $(this).attr('action')

    // check whether browser fully supports all File API
    if (window.File && window.FileReader && window.FileList && window.Blob && fileElement[0].files.length > 0) {
      var fsize = fileElement[0].files[0].size
      if (fsize > 15 * 1024 * 1024) {
        window.location = actionUrl + '?error=tooBig'
        return
      }
    }

    this.submit()
  })
}
