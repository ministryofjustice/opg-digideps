/*jshint browser: true */
(function () {
    "use strict";

    var root = this,
        $ = root.jQuery;

    if (typeof GOVUK === 'undefined') {
        root.GOVUK = {};
    }

    var uploadFile = function (containerSelector) {
        $(containerSelector).on('click', function(){
            var fileName = $('#report_document_upload_file').val();
            if (fileName) {
                $('#upload-progress').removeClass('hidden');
            }
        });
    };

    root.GOVUK.uploadFile = uploadFile;

}).call(this);

/*jshint browser: true */
(function () {
    "use strict";

    var root = this,
        $ = root.jQuery;

    if (typeof GOVUK === 'undefined') {
        root.GOVUK = {};
    }

    var uploadProgress = function () {
        // check if exists
        if ($('#uploadProgress').length == 1) {
            console.log($('#uploadProgress').attr('max'));
            var nOfChunks = $('#uploadProgress').attr('max') - 1;
            console.log(nOfChunks);
            var casrecTruncateAjax = $('#uploadProgress').data('path-casrec-truncate-ajax');
            console.log(casrecTruncateAjax);

            $.ajax({
                url: casrecTruncateAjax,
                dataType: 'json'
            }).done(function (data) {
                $('#uploadProgress').val(1);
                uploadChunk(0,nOfChunks);
            });
        }
    };

    var uploadChunk = function(currentChunk,nOfChunks) {
        var casrecAddAjax = $('#uploadProgress').data('path-casrec-add-ajax');
        var casrecUpload = $('#uploadProgress').data('path-casrec-upload');
        console.log(casrecUpload);
        if (currentChunk < nOfChunks ) {
            $.ajax({
                url: casrecAddAjax + "?chunk=" + currentChunk,
                dataType: 'json'
            }).done(function (data) {
                $('#uploadProgress').val(currentChunk + 1);
                uploadChunk(currentChunk + 1);
            }).error(function() {
                alert('Upload error. please try uploading again');
            });
        } else {
            console.log('done');
            window.location.href = casrecUpload;
        }
    };

    root.GOVUK.uploadProgress = uploadProgress;

}).call(this);
