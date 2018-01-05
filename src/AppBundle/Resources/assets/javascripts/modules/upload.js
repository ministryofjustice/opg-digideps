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
        $.ajax({
            url: "{{ path('casrec_truncate_ajax') }}",
            dataType: 'json'
        }).done(function (data) {
            $('#uploadProgress').val(1);
            uploadChunk(0);
        });

        function uploadChunk(currentChunk) {
            if (currentChunk < {{ nOfChunks }}) {
                $.ajax({
                    url: "{{ path('casrec_add_ajax') }}?chunk=" + currentChunk,
                    dataType: 'json'
                }).done(function (data) {
                    $('#uploadProgress').val(currentChunk + 1);
                    uploadChunk(currentChunk + 1);
                }).error(function() {
                    alert('Upload error. please try uploading again');
                });
            } else {
                window.location.href = "{{ path('casrec_upload') }}";
            }
        }

    };


    root.GOVUK.uploadProgress = uploadProgress;

}).call(this);
