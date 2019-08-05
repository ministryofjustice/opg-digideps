/*jshint browser: true */
(function () {
    "use strict";

    var root = this,
        $ = root.jQuery;

    if (typeof GOVUK === 'undefined') {
        root.GOVUK = {};
    }

    var uploadProgress = function (element) {
        // check if exists
        if ($(element).length == 1) {
            var nOfChunks = $(element).attr('max') - 1;
            var casrecTruncateAjaxUrl = $(element).data('path-casrec-truncate-ajax');

            $.ajax({
                url: casrecTruncateAjaxUrl,
                dataType: 'json'
            }).done(function (data) {
                $(element).val(1);
                uploadChunk(0,nOfChunks,element);
            });
        }
    };

    var uploadChunk = function(currentChunk,nOfChunks,element) {
        var casrecAddAjaxUrl = $(element).data('path-casrec-add-ajax');
        var casrecUploadUrl = $(element).data('path-casrec-upload');

        if (currentChunk < nOfChunks) {
            $.ajax({
                url: casrecAddAjaxUrl + "?chunk=" + currentChunk,
                dataType: 'json'
            }).done(function (data) {
                $(element).val(currentChunk + 1);
                uploadChunk(currentChunk + 1,nOfChunks,element);
            }).error(function() {
                alert('Upload error. please try uploading again');
            });
        } else {
            window.location.href = casrecUploadUrl;
        }
    };

    root.GOVUK.uploadProgress = uploadProgress;

}).call(this);

/*jshint browser: true */
(function () {
    "use strict";

    var root = this,
        $ = root.jQuery;

    if (typeof GOVUK === 'undefined') {
        root.GOVUK = {};
    }

    var uploadProgressPA = function (element) {
        // check if exists
        if ($(element).length == 1) {
            var nOfChunks = $(element).attr('max') - 1;

            $(window).on('load', (function () {
                setTimeout(function () {
                    uploadChunk(0,nOfChunks,element);
                }, 50);
            }));
        }
    };

    var uploadChunk = function(currentChunk,nOfChunks,element) {
        var adminPaUploadUrl = $(element).data('path-admin-pa-upload');
        var paAddAjaxUrl = $(element).data('path-pa-add-ajax');

        if (currentChunk == nOfChunks + 1) {
            window.location.href = adminPaUploadUrl;
            return;
        }

        $.ajax({
            url: paAddAjaxUrl + "?chunk=" + currentChunk,
            method: "POST",
            async: false,
            dataType: 'json',
            success: function (data) {
                $(element).val(currentChunk);
            }
        });

        // launch next
        setTimeout(function () {
            uploadChunk(currentChunk + 1,nOfChunks,element);
        }, 100);
    };

    root.GOVUK.uploadProgressPA = uploadProgressPA;

}).call(this);

/*jshint browser: true */
(function () {
    "use strict";

    var root = this,
        $ = root.jQuery;

    if (typeof GOVUK === 'undefined') {
        root.GOVUK = {};
    }

    var uploadFile = function (containerSelector) {

        // Show in progress message
        $(containerSelector).on('click', function(){
            var fileName = $('#report_document_upload_file').val();
            if (fileName) {
                $('#upload-progress').removeClass('hidden');
            }
        });

        // Show an error if file is over 15mb
        $('#upload_form').on('submit', function (e) {
            e.preventDefault();
            var fileElement = $('#report_document_upload_file');
            var actionUrl = $(this).attr('action');

            // check whether browser fully supports all File API
            if (window.File && window.FileReader && window.FileList && window.Blob && fileElement[0].files.length > 0) {
                var fsize = fileElement[0].files[0].size;
                if (fsize > 15 * 1024 * 1024 ) {
                    window.location = actionUrl + '?error=tooBig';
                    return;
                }
            }

            this.submit();
        });
    };

    root.GOVUK.uploadFile = uploadFile;

}).call(this);

