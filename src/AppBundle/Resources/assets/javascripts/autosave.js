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
        
    };
    
    var NONE =  {label:'', state:''};
    var SAVING =  {label:'Saving...', state:'saving'};
    var SAVED =  {label:'Saved', state:'saved'};
    var NOTSAVED =  {label:'Not saved', state:'notsaved'};
    
    AutoSave.prototype.addEventHandlers = function () {
        this.blurHandler = this.getBlurHandler();
        this.submitHandler = this.getSubmitHandler();
        this.keypressHandler = this.getKeyPressHandler();
        this.pasteHandler = this.getPasteHandler();
        
        this.form.on('submit', this.submitHandler);
        this.form.find('input,textarea')
            .on('blur', this.blurHandler)
            .on('keypress', this.keypressHandler)
            .on('paste', this.pasteHandler);
    };
    
    AutoSave.prototype.getBlurHandler = function () {
        return function (e) {
            e.preventDefault();
            if (this.saved === false) {
                this.save();
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
    AutoSave.prototype.getKeyPressHandler = function () {
        return function (event) {
            var char;
            if (event.which === null) {
                char = event.keyCode;    // old IE
            } else if (event.which !== 0) {
                char = event.which;	  // All others
            } else {
                return;
            }
            
            if (char >= 48 && char <= 57 || char === 190 || char === 188) {
                this.saved = false;
                this.displayStatus(NONE);
            }
            
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
            fail: saveFail
        });
 
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
    };
    AutoSave.prototype.handleSaveError = function (data) {
        this.displayStatus(NOTSAVED);
        if (data.errors.errorCode === 1001 && data.errors.hasOwnProperty('fields')) {
            this.showFieldErrors(data.errors.fields);
        }
    };
    AutoSave.prototype.displayStatus = function (state) {
        this.statusElement.text(state.label);
        this.statusElement.attr('status',state.state);
    };
    
    root.GOVUK.AutoSave = AutoSave;
    
}).call(this);
