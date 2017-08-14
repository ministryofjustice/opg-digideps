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
