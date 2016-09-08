/*jshint browser: true */
(function () {
    "use strict";

    var root = this,
        $ = root.jQuery;

    if (typeof GOVUK === 'undefined') {
        root.GOVUK = {};
    }

    var ExpandingMoreDetails = function (containerSelector) {
        var container = $(containerSelector);
        var inputBox = container.find('input.transaction-value');
        var textareaGroup = container.find('textarea.transaction-more-details').parents('.form-group');

        // more details
        inputBox.on('keyup input paste change', function (event) {
            var value = parseFloat($(event.target).val().replace(/,/g, ""));
            //console.log(value);
            if (!isNaN(value) && value !== 0) {
                textareaGroup.removeClass('hidden');
            } else {
                textareaGroup.addClass('hidden');
            }
        }).trigger('keyup');
    };

    root.GOVUK.ExpandingMoreDetails = ExpandingMoreDetails;

}).call(this);
