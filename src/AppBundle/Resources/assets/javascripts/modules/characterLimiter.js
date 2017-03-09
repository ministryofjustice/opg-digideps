// CHARACTER LIMITER
// Use the class name of .js-limit-chars-x on the input
// The 'x' will be the character limit

/*jshint browser: true */
(function () {
    "use strict";

    var root = this,
        $ = root.jQuery;

    if (typeof GOVUK === 'undefined') {
        root.GOVUK = {};
    }

    var limitChars = function (containerSelector) {
        var limitElement = $(containerSelector).find("[class*='js-limit-chars-']");

        limitElement.on('keyup input paste change', function (event) {
            var $this = $(event.target);
            // Get the classes
            var elClass = $this.attr('class');
            // Get the limiter value (the 'x')
            var charsLimit = parseInt(elClass.substr(elClass.indexOf("limit-chars-")+12,1));
            // The amount of chars in the input
            var chars = $this.val().length;

            if(chars <= charsLimit){
                return true;
            } else {
                var str = $this.val();
                str = str.substring(0, str.length - 1);
                $this.val(str);
            }

        }).trigger('keyup');
    };

    root.GOVUK.limitChars = limitChars;

}).call(this);