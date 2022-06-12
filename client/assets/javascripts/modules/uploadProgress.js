/* globals $ */
const uploadProgress = function (element) {
  // check if exists
  if ($(element).length === 1) {
    const nOfChunks = $(element).attr('max') - 1
    const preRegistrationDeleteUrl = $(element).data('path-pre-registration-delete-ajax')

    $.ajax({
      url: preRegistrationDeleteUrl,
      dataType: 'json'
    }).done(function (data) {
      $(element).val(1)
      uploadChunk(0, nOfChunks, element)
    })
  }
}

const uploadChunk = function (currentChunk, nOfChunks, element) {
  const preRegistrationAddAjaxUrl = $(element).data('path-pre-registration-add-ajax')
  const preRegistrationUploadUrl = $(element).data('path-pre-registration-upload')

  if (currentChunk < nOfChunks) {
    $.ajax({
      url: preRegistrationAddAjaxUrl + '?chunk=' + currentChunk,
      dataType: 'json'
    })
      .done(function (data) {
        $(element).val(currentChunk + 1)
        uploadChunk(currentChunk + 1, nOfChunks, element)
      })
      .fail(function (jqXHR, textStatus, errorThrown) {
        window.alert(`Upload error. please try uploading again: ${textStatus}`)
      })
  } else {
    window.location.href = `${preRegistrationUploadUrl}?complete=1`
  }
}

module.exports = uploadProgress
