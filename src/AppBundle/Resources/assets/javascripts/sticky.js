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
        this.top = $(element).offset().top - 20;
        this.addEventHandlers();
        this.fieldFocused = false;
    };

    StickyHeader.prototype.addEventHandlers = function () {
        if (mobileSafari) {
            this.blurHandler = this.getInputBlurEventHandler();
            this.focusHandler = this.getInputFocusEventHandler();
            $('input, textarea').on('blur', this.blurHandler);
            $('input, textarea').on('focus', this.focusHandler);
        }
        this.windowScrollHandler = this.getWindowScrollEventHandler();
        $(window).on('scroll', this.windowScrollHandler);
    };
    StickyHeader.prototype.getWindowScrollEventHandler = function () {
        return function () {
            this.handleWindowScroll();
        }.bind(this);
    };
    StickyHeader.prototype.getInputFocusEventHandler = function () {
        return function () {
            this.handleFocus();
        }.bind(this);
    };
    StickyHeader.prototype.getInputBlurEventHandler = function () {
        return function () {
            this.handleBlur();
        }.bind(this);
    };
    StickyHeader.prototype.handleFocus = function () {
        this.fieldFocused = true;
        this.handleWindowScroll();
    };
    StickyHeader.prototype.handleBlur = function () {
        this.fieldFocused = false;
        this.handleWindowScroll();
    };
    StickyHeader.prototype.handleWindowScroll = function () {
        if (window.pageYOffset >= this.top) {
            body.addClass('fixed');

            // Mobile safari hides fixed elements when the keyboard is shown so use
            // absolute instead.
            if (mobileSafari && this.fieldFocused) {
                this.wrapper.css({
                    position: 'absolute',
                    top: window.pageYOffset + 'px',
                    left: 0
                });
            } else {
                this.wrapper.attr('style','');
            }

        } else {
            body.removeClass('fixed');
            if (mobileSafari) {
                this.wrapper.attr('style','');
            }
        }
    };

    root.GOVUK.StickyHeader = StickyHeader;

}).call(this);
