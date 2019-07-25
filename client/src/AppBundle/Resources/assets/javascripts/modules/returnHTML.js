/*jshint browser: true */
(function () {
    "use strict";

    var root = this,
        $ = root.jQuery;

    if (typeof GOVUK === 'undefined') {
        root.GOVUK = {};
    }

    var returnHTML = function (containerSelector) {
        $(containerSelector).on('click', function(e){
            e.preventDefault();
            var link = $(this);
            $.get(link.attr('href'), function (data) {
                link.replaceWith(data);
            }, "html");
        });
    };

    root.GOVUK.returnHTML = returnHTML;

}).call(this);
