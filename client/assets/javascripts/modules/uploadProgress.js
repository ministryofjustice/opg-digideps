/* globals $ */
const uploadProgress = function (element) {
  // check if exists
  if ($(element).length === 1) {
    const nOfChunks = $(element).attr('max') - 1
    const casrecDeleteUrl = $(element).data('path-casrec-ajax')

    $.ajax({
      url: casrecDeleteUrl,
      dataType: 'json'
    }).done(function (data) {
      $(element).val(1)
      uploadChunk(0, nOfChunks, element)
    })
  }
}

const uploadChunk = function (currentChunk, nOfChunks, element) {
  const casrecAddAjaxUrl = $(element).data('path-casrec-add-ajax')
  const casrecUploadUrl = $(element).data('path-casrec-upload')

  if (currentChunk < nOfChunks) {
    $.ajax({
      url: casrecAddAjaxUrl + '?chunk=' + currentChunk,
      dataType: 'json'
    })
      .done(function (data) {
        $(element).val(currentChunk + 1)
        uploadChunk(currentChunk + 1, nOfChunks, element)
      })
      .error(function () {
        window.alert('Upload error. please try uploading again')
      })
  } else {
    window.location.href = casrecUploadUrl
  }
}

module.exports = uploadProgress
