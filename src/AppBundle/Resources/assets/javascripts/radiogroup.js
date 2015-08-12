var opg = opg || {};

// call with a selector for a group that 
(function ($, opg) {

    var FOCUSEDCLASS = 'focused',
        SELECTEDCLASS = 'selected';


    var RadioGroup = function(element, opts) {
        this.fieldset = $(element);
        this.formControls = this.fieldset.find('.form-control.radio-vertical');
        this.radios = this.fieldset.find('input');
        this.addEvents();

    };
        
    RadioGroup.prototype.markSelected = function(radio) {
        var current = this.fieldset.find('.' + SELECTEDCLASS);
        current.removeClass(SELECTEDCLASS);
        radio.addClass(SELECTEDCLASS);
        radio.find('input').prop('checked', true);
    };
    RadioGroup.prototype.markFocused = function(target, state) {
        if (state === 'focused') {
          target.parent().parent().addClass(FOCUSEDCLASS);
        } else {
          target.parent().parent().removeClass(FOCUSEDCLASS);
        }
    };
    
    RadioGroup.prototype.getClickHandler = function () {
        return function (e) {
            this.markSelected($(e.currentTarget));
        }.bind(this);
    };
    RadioGroup.prototype.getFocusHandler = function (opts) {
        var focusEvent = (opts.level === 'document') ? 'focusin' : 'focus';

        return function (e) {
            var state = (e.type === focusEvent) ? 'focused' : 'blurred';
            this.markFocused($(e.target), state);
        }.bind(this);
    };
    
    RadioGroup.prototype.addEvents = function () {
        this.clickHandler = this.getClickHandler();
        this.focusHandler = this.getFocusHandler({ 'level' : 'element' });

        this.formControls.on('click', this.clickHandler);
        this.radios.on('focus blur', this.focusHandler);
    };
        
    opg.RadioGroup = RadioGroup;


})(jQuery, opg);