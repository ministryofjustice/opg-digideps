var opg = opg || {};

// call with selector for radio button and selection for rest of form container
// and other options
// new RadioHide({'radio':'#radio','content':'#content'});
// Default is hide on NO, show on yes unless hideOnYes:true
// No values makes it hide by default.
(function ($, opg) {

    var RadioHide = function(options) {
        this.radio = $(options.radio);
        this.content = $(options.content);
        this.hideOnYes = options.hideOnYes || false;
        this.hideByDefault = options.hideByDefault || true;

        this.changeHandler = this.getUpdateHandler();
        this.radio.change(this.changeHandler);

        if (this.hideByDefault) {
            this.content.hide();
        }
    };

    RadioHide.prototype.hideContent = function () {
        this.content.hide();
    };
    RadioHide.prototype.showContent = function () {
        this.content.show();
    };
    
    RadioHide.prototype.updateView = function () {
    
        var value;
        for (var iPos = 0; iPos < this.radio.length; iPos += 1) {
            if (this.radio[iPos].checked === true) {
                value = this.radio[iPos].value;
            }
        }

        if (value === undefined) {
            if (this.hideByDefault === true) {
                this.hideContent();
            } else {
                this.showContent();
            }
        } else if (value == 'yes') {
            if (this.hideOnYes) {
                this.showContent();
            } else {
                this.hideContent();
            }
        } else {
            if (this.hideOnYes) {
                this.hideContent();
            } else {
                this.showContent();
            }
        } 
    
    };

    RadioHide.prototype.getUpdateHandler = function () {
        return function(e) {
            this.updateView();
        }.bind(this);
    };

    opg.RadioHide = RadioHide;


})(jQuery, opg);