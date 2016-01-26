/*jshint browser: true */
(function () {
    "use strict";
    
    var root = this,
        $ = root.jQuery,
        body = $('body'),
        mobileSafari = navigator.userAgent.match(/(iPod|iPhone|iPad)/) && navigator.userAgent.match(/AppleWebKit/);

    if (typeof GOVUK === 'undefined') { root.GOVUK = {}; }

    var StickyHeader = function(element) {
        this.wrapper = $(element);
        this.top = $(element).offset().top - 30;
        this.addEventHandlers();
    };
    
    StickyHeader.prototype.addEventHandlers = function () {
        this.windowScrollHandler = this.getWindowScrollEventHandler();
        document.addEventListener("scroll", this.windowScrollHandler, false);
    };
    StickyHeader.prototype.getWindowScrollEventHandler = function () {
        return function (e) {
            this.handleWindowScroll($(e.target));
        }.bind(this);
    };
    
    StickyHeader.prototype.handleWindowScroll = function () {
        if (window.pageYOffset >= this.top) {
            body.addClass('fixed');
            
            // Mobile safari hides fixed elements when the keyboard is shown so use
            // absolute instead.
            if (mobileSafari) {
                this.wrapper.css({
                    position: 'absolute',
                    top: window.pageYOffset + 'px',
                    left: 0
                });
            }
            
        } else {
            body.removeClass('fixed');
            if (mobileSafari) {
                this.wrapper.css({
                    position: 'static'
                });
            }
        }
    };

    root.GOVUK.StickyHeader = StickyHeader;

}).call(this);
