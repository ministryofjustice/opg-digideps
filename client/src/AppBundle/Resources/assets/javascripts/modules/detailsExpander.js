/*jshint browser: true */
(function () {
    "use strict";

    var root = this,
        $ = root.jQuery;

    if (typeof GOVUK === 'undefined') {
        root.GOVUK = {};
    }

    var detailsExpander = function (containerSelector) {
        var container = $(containerSelector);
        var inputBox = container.find('input[type="text"]');
        var textareaGroup = container.find('textarea').parents('.form-group, .govuk-form-group');

        // more details
        inputBox.on('keyup input paste change', function (event) {
            var value = parseFloat($(event.target).val().replace(/,/g, ""));
            //console.log(value);
            if (!isNaN(value) && value !== 0) {
                textareaGroup.removeClass('js-hidden');
                textareaGroup.parent().removeClass('js-hidden');
            } else {
                textareaGroup.addClass('js-hidden');
            }
        }).trigger('keyup');
    };

    root.GOVUK.detailsExpander = detailsExpander;

}).call(this);
