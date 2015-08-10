var opg = opg || {};

// call with selector for radio button and selection for rest of form container
// and other options
// new RadioHide({'radio':'#radio','content':'#content'});
// Default is hide on NO, show on yes unless hideOnYes:true
(function ($, opg) {

    var RadioHide = function(options) {
        this.init(options);
    }

    Radiohide.prototype.init = function (options) {
    }



    opg.radioHide = radioHide;


})(jQuery, opg);