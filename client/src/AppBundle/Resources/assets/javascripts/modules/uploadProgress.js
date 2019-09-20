/* globals $ */
var uploadProgress = function (element) {
  // check if exists
  if ($(element).length === 1) {
    var nOfChunks = $(element).attr('max') - 1
    var casrecTruncateAjaxUrl = $(element).data('path-casrec-truncate-ajax')

    $.ajax({
      url: casrecTruncateAjaxUrl,
      dataType: 'json'
    }).done(function (data) {
      $(element).val(1)
      uploadChunk(0, nOfChunks, element)
    })
  }
}

var uploadChunk = function (currentChunk, nOfChunks, element) {
  var casrecAddAjaxUrl = $(element).data('path-casrec-add-ajax')
  var casrecUploadUrl = $(element).data('path-casrec-upload')

  if (currentChunk < nOfChunks) {
    $.ajax({
      url: casrecAddAjaxUrl + '?chunk=' + currentChunk,
      dataType: 'json'
    }).done(function (data) {
      $(element).val(currentChunk + 1)
      uploadChunk(currentChunk + 1, nOfChunks, element)
    }).error(function () {
      window.alert('Upload error. please try uploading again')
    })
  } else {
    window.location.href = casrecUploadUrl
  }
}

module.exports = uploadProgress
