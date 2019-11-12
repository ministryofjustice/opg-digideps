/* globals $ */
var uploadProgressPA = function (element) {
  // check if exists
  if ($(element).length === 1) {
    var nOfChunks = $(element).attr('max') - 1

    $(window).on('load', function () {
      setTimeout(function () {
        uploadChunk(0, nOfChunks, element)
      }, 50)
    })
  }
}

var uploadChunk = function (currentChunk, nOfChunks, element) {
  var csvType = $(element).data('csv-type')
  var adminPaUploadUrl = $(element).data('path-admin-pa-upload')
  var paAddAjaxUrl = $(element).data('path-pa-add-ajax')

  if (currentChunk === nOfChunks + 1) {
    window.location.href = adminPaUploadUrl
    return
  }

  $.ajax({
    url: paAddAjaxUrl + '?csvType=' + csvType + '&chunk =' + currentChunk,
    method: 'POST',
    async: false,
    dataType: 'json',
    success: function (data) {
      $(element).val(currentChunk)
    }
  })

  // launch next
  setTimeout(function () {
    uploadChunk(currentChunk + 1, nOfChunks, element)
  }, 100)
}

module.exports = uploadProgressPA
