var opg = opg || {};

// call with a selector for a group that 
(function ($, opg) {

    var RadioGroup = function(element, opts) {
        this.fieldset = $(element);
        this.formControls = this.fieldset.find('.form-control.radio-vertical');
        this.radios = this.fieldset.find('input');
        this.addEvents();
        this.focusedClass = 'focused';
        this.selectedClass = 'selected';
    };
        
    RadioGroup.prototype.markSelected = function(target) {
        this.fieldset.find('.selected').removeClass(this.selectedClass);
        target.parent().parent().addClass(this.selectedClass);
    };
    RadioGroup.prototype.markFocused = function(target, state) {
        if (state === 'focused') {
          target.parent().parent().addClass(this.focusedClass);
        } else {
          target.parent().parent().removeClass(this.focusedClass);
        }
    };
    
    RadioGroup.prototype.getClickHandler = function () {
        return function (e) {
            this.markSelected($(e.target));
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