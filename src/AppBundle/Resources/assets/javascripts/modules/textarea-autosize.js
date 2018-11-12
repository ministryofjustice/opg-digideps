// Auto size textarea
// Use the class name of .js-auto-size on the textarea
// Note that associated styles live under _.forms.scss

(function () {
    // "use strict";

    var root = this,
        $ = root.jQuery;

    if (typeof GOVUK === 'undefined') {
        root.GOVUK = {};
    }

    var textAreaAutoSize = function (containerSelector) {
        var textArea = $(containerSelector).find("[class*='js-auto-size'] textarea");

        textArea.on('keyup input paste change', function (event) {
            var $this = $(event.target);

            setTimeout(function(){
                $this.css('height', 'auto');
                $this.css('height', $this.prop('scrollHeight') + 'px');
            },0);
        }).trigger('keyup');
    };

    root.GOVUK.textAreaAutoSize = textAreaAutoSize;

}).call(this);
