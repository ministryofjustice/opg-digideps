/*jshint browser: true */
(function () {
    "use strict";
    
    var root = this,
        $ = root.jQuery;
    
    if (typeof GOVUK === 'undefined') { root.GOVUK = {}; }

    var AutoSave = function(options) {

        this.form = $(options.form);
        this.statusElement = $(options.statusElement);
        this.url = options.url;
        this.saved = true;

        this.addEventHandlers();

        this.form.find('button[type="submit"]').hide();
        
    };
    
    var NONE =  {label:'', state:''};
    var SAVING =  {label:'Saving...', state:'saving'};
    var SAVED =  {label:'Saved', state:'saved'};
    var NOTSAVED =  {label:'Not saved', state:'notsaved'};
    
    AutoSave.prototype.addEventHandlers = function () {
        this.blurHandler = this.getBlurHandler();
        this.submitHandler = this.getSubmitHandler();
        this.changeHandler = this.getChangeHandler();
        this.pasteHandler = this.getPasteHandler();
        
        this.form.on('submit', this.submitHandler);
        this.form.find('input,textarea')
            .on('blur', this.blurHandler)
            .on('change', this.changeHandler)
            .on('paste', this.pasteHandler);
    };
    
    AutoSave.prototype.getBlurHandler = function () {
        return function (e) {
            e.preventDefault();
            if (this.saved === false) {
                this.save();
                this.formatCurrency(e.target);
            }
            return true;
        }.bind(this);
    };
    AutoSave.prototype.getSubmitHandler = function () {
        return function (e) {
            e.preventDefault();
            if (this.saved === false) {
                this.save();
            }
            // redirect to desired location
            return false;
        }.bind(this);
    };
    AutoSave.prototype.getChangeHandler = function () {
        return function (event) {
            this.saved = false;
            this.displayStatus(NONE);
            this.clearErrorsOnField($(event.target));
        }.bind(this);  
    };
    AutoSave.prototype.getPasteHandler = function () {
        return function () {
           this.saved = false;
           this.displayStatus(NONE);
        }.bind(this);
    };
    
    AutoSave.prototype.save = function () {
        this.displayStatus(SAVING);
        var data = this.form.serialize();
        var saveDone = this.handleSaveDone.bind(this);
        var saveFail = this.handleSaveError.bind(this);
        
        $.ajax({
            type: 'PUT',
            url: this.url,
            data: data,
            success: saveDone,
            error: saveFail
        });
 
    };
    AutoSave.prototype.clearErrors = function () {
        this.form.find('.error-message').remove();
        this.form.find('.error').removeClass('error');
    };
    AutoSave.prototype.clearErrorsOnField = function(fieldElement) {
        var group = fieldElement.parent();
        group.removeClass('error');
        group.find('.error-message').remove();
    };
    AutoSave.prototype.showFieldErrors = function (errors) {
        var group, label;
        
        $.each(errors, function(key, value) {
            group = $('#' + key).parent();
            label = group.find('label').eq(0);
            
            group.addClass('error');
            
            $('<span/>')
                .text(value)
                .addClass('error-message')
                .insertAfter(label);
            
        });

    };
    AutoSave.prototype.handleSaveDone = function () {
        this.saved = true;
        this.displayStatus(SAVED);
        this.clearErrors();
    };
    AutoSave.prototype.handleSaveError = function (resp) {
        this.saved = true;
        this.displayStatus(NOTSAVED);
        this.clearErrors();
        var data = resp.responseJSON;
        if (data.errors.errorCode === 1001 && data.errors.hasOwnProperty('fields')) {
            this.showFieldErrors(data.errors.fields);
        }
    };
    AutoSave.prototype.displayStatus = function (state) {
        this.statusElement.text(state.label);
        this.statusElement.attr('data-status',state.state);
    };
    AutoSave.prototype.formatCurrency = function (element) {
        element = $(element);
        var number = element.val();
        
        var decimalplaces = 2;
        var decimalcharacter = ".";
        var thousandseparater = ",";
        number = parseFloat(number);
        
        var formatted = String(number.toFixed(decimalplaces));
        if( decimalcharacter.length && decimalcharacter != "." ) { formatted = formatted.replace(/\./,decimalcharacter); }
        var integer = "";
        var fraction = "";
        var strnumber = String(formatted);
        var dotpos = decimalcharacter.length ? strnumber.indexOf(decimalcharacter) : -1;
        if( dotpos > -1 ) {
            if( dotpos ) { integer = strnumber.substr(0,dotpos); }
            fraction = strnumber.substr(dotpos+1);
        }
        else { integer = strnumber; }
        if( integer ) { integer = String(Math.abs(integer)); }
        while( fraction.length < decimalplaces ) { fraction += "0"; }
        var temparray = [];
        
        while( integer.length > 3 ) {
            temparray.unshift(integer.substr(-3));
            integer = integer.substr(0,integer.length-3);
        }
        
        temparray.unshift(integer);
        integer = temparray.join(thousandseparater);
        element.val( integer + decimalcharacter + fraction);
        
    };
    root.GOVUK.AutoSave = AutoSave;
    
}).call(this);
