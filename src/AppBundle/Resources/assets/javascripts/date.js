var opg = opg || {};

(function ($, opg) {

    function leapYear(year) {
      return ((year % 4 === 0) && (year % 100 !== 0)) || (year % 400 === 0);
    }

    opg.DateValidate = function(target) {
        var _this = this, dateParts;
        
        this.container = $(target);
        
        dateParts = this.container.find('input');
        this.dayInput = $(dateParts[0]);
        this.monthInput = $(dateParts[1]);
        this.yearInput = $(dateParts[2]);

        dateParts.on('propertychange input', function(event) {
            _this.skipToNext.call( _this, event);
        });
        dateParts.on('blur', function(event) {
            _this.validate.call( _this, event);
            _this.pad.call( _this, event);
        });
    };
    
    
    opg.DateValidate.prototype.addErrorSection = function() {
        this.container.find('.errors').remove();
        this.container.find('fieldset .form-group').first().before('<ul class="errors"><li class="error-message">Invalid Date</li></ul>');
    };
    opg.DateValidate.prototype.pad = function(event) {
        var field = $(event.target);
        var str = field.val();
        
        if (field === this.yearInput || str === '' || str.length == 2) {
            return;
        }
    
        var value = parseInt(field.val(), 10);
     
        if (value < 10 && value > 0) {
            var newValue = '0' + field.val();
            field.val(newValue);
        }
        
    };
    opg.DateValidate.prototype.skipToNext = function(event) {
        var target = $(event.target);
        
        if(target.val().length === parseInt(target.attr('maxlength'), 10)) {
            if (target[0] === this.dayInput[0]) {
                this.dayInput.trigger('blur');
                this.monthInput.focus();
            } else if (target[0] === this.monthInput[0]) {
                this.monthInput.trigger('blur');
                this.yearInput.focus();
            } else if (target[0] == this.yearInput[0]) {
                this.validate(event);
            }
        } 

    };
    opg.DateValidate.prototype.showError = function(field) {
        $(field).parent().addClass('field-with-errors');
        this.container.addClass('field-with-errors');
        this.addErrorSection();
    };
    opg.DateValidate.prototype.clearErrors = function() {
        this.container.find('.errors').remove();
        this.container.removeClass('field-with-errors');
        this.container.find('.field-with-errors').removeClass('field-with-errors');
    };
    opg.DateValidate.prototype.validate = function(event) {

        this.clearErrors();

        var field = $(event.target);
        var parent = field.parent();
        var dayStr, dayValue, monthStr, monthValue, yearStr, yearValue;
                
        dayStr = this.dayInput.val();
        if (dayStr.length > 0) {
            dayValue = parseInt(dayStr, 10);
            if (isNaN(dayValue) || dayValue < 1 || dayValue > 31) {
                this.showError(this.dayInput);
            }
        }

        monthStr = this.monthInput.val();
        if (monthStr.length > 0) {
            monthValue = parseInt(monthStr);
            if (isNaN(monthValue) || monthValue < 1 || monthValue > 12 ) {
                this.showError(this.monthInput);
            }
            if (dayValue > 29 && monthValue === 2 ||
                dayValue === 31 && (monthValue === 9 || monthValue === 4 || monthValue === 6 || monthValue === 11)) 
            {
                this.showError(this.dayInput);
            }
        }
        
        yearStr = this.yearInput.val();
        
        if (yearStr.length > 0) {
            yearValue = parseInt(yearStr, 10);
            if(isNaN(yearValue) || yearValue < 1800) {
                this.showError(this.yearInput);
            } else if(!isNaN(yearValue) && (!leapYear(yearValue) && dayValue === 29)) {               
                this.showError(this.dayInput);
            }
        }
        
    };

})(jQuery, opg);