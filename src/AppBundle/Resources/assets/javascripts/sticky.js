/*jshint browser: true */
(function () {
    "use strict";

    var root = this,
        $ = root.jQuery,
        body = $('body');

    if (typeof GOVUK === 'undefined') { root.GOVUK = {}; }

    var StickyHeader = function(element) {
        this.top = $(element).offset().top - 30;
        this.addEventHandlers();
    };
    
    StickyHeader.prototype.addEventHandlers = function () {
        this.windowScrollHandler = this.getWindowScrollEventHandler();
        window.onscroll = this.windowScrollHandler;
    };
    StickyHeader.prototype.getWindowScrollEventHandler = function () {
        return function (e) {
            this.handleWindowScroll($(e.target));
        }.bind(this);
    };
    StickyHeader.prototype.handleWindowScroll = function () {
        if (window.pageYOffset >= this.top) {
            body.addClass('fixed');
        } else {
            body.removeClass('fixed');
        }
    };

    root.GOVUK.StickyHeader = StickyHeader;

}).call(this);
