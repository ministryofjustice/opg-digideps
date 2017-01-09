/* globals jQuery: true, GOVUK: true */
/* jshint browser: true */
if (typeof GOVUK === 'undefined') {
    GOVUK = {};
}

(function ($, GOVUK) {
    "use strict";

    var AutoSave = function(options) {

        this.form = $(options.form);
        this.statusElement = $(options.statusElement);
        this.url = options.url;
        this.saved = true;

        this.blurHandler = this.blurHandler.bind(this);
        this.submitHandler = this.submitHandler.bind(this);
        this.keyDownHandler = this.keyDownHandler.bind(this);
        this.keyPressHandler = this.keyPressHandler.bind(this);
        this.pasteHandler = this.pasteHandler.bind(this);
        this.addField = this.addField.bind(this);
        this.removeField = this.removeField.bind(this);

        this.addEventHandlers();

        this.form.find('button[type="submit"]').hide();

        this.preprocessor = options.preprocessor||null;

    };

    var NONE =  {label:'', state:''};
    var SAVING =  {label:'Saving...', state:'saving'};
    var SAVED =  {label:'Saved', state:'saved'};
    var NOTSAVED =  {label:'Not saved', state:'notsaved'};

    var ignoreCodes = [93, 13, 9, 35, 36, 45,34, 33, 37, 38, 39, 40, 27, 44, 145,19, 125, 124,126];

    AutoSave.prototype.addEventHandlers = function () {
        this.form.on('submit', this.submitHandler)
            .on('addField', this.addField)
            .on('removeField', this.removeField);

        this.form.find('input,textarea')
            .on('blur', this.blurHandler)
            .on('keypress', this.keyPressHandler)
            .on('keydown', this.keyDownHandler)
            .on('paste', this.pasteHandler);
    };

    AutoSave.prototype.blurHandler = function (event) {
        event.preventDefault();
        if (this.saved === false) {
            this.save(event);
        }
        return true;
    };
    AutoSave.prototype.submitHandler = function (event) {
        event.preventDefault();
        if (this.saved === false) {
            this.save(event);
        }
        // redirect to desired location
        return false;
    };
    // Have to use a combination of keydown AND keypress to capture backspace on all browsers
    AutoSave.prototype.keyDownHandler = function (event) {
        if (event.keyCode === 8 || event.keyCode === 46) {    // If the user presses backspace
            this.keyPressHandler(event);
        }
    };
    AutoSave.prototype.keyPressHandler = function (event) {
        var char;
        if (event.which === null) {
            char = event.keyCode;    // old IE
        } else if (event.which !== 0) {
            char = event.which;	  // All others
        } else {
            return;
        }

        // If the user entered a key that affects the field value then mark things as changed
        // otherwise ignore it, for things like tab or arrow keys
        if ($.inArray(char, ignoreCodes) === -1 || (event.target.tagName === 'TEXTAREA' && char === 13)) {
            this.saved = false;
            this.displayStatus(NONE);
            this.clearErrorsOnField($(event.target));
            this.startSaveTimer();
        }
    };
    AutoSave.prototype.pasteHandler = function () {
       this.saved = false;
       this.displayStatus(NONE);
       this.clearErrorsOnField($(event.target));
       this.startSaveTimer();
    };

    AutoSave.prototype.save = function (event) {
        this.displayStatus(SAVING);
        this.clearSaveTimer();

        if (this.preprocessor) {
            this.preprocessor(this.form);
        }

        var data = (typeof event === "undefined") ? this.form.serialize() :
                        $(event.target).parents('div.transaction').find('input,textarea')
                        .add('#transactions__token')
                        .add('#transactions_id')
                        .serialize();
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
    AutoSave.prototype.startSaveTimer = function () {
        var self = this;
        this.clearSaveTimer();
        this.timer = window.setTimeout(function () {
            if (self.saved === false) {
                self.save();
            }
        }, 5000);
    };
    AutoSave.prototype.clearSaveTimer = function () {
        if (this.timer) {
            window.clearTimeout(this.timer);
            this.timer = null;
        }
    };

    AutoSave.prototype.addField = function (event) {
      //console.log('autosave-addfield:', event);
      $(event.target)
          .on('blur', this.blurHandler)
          .on('keypress', this.keyPressHandler)
          .on('keydown', this.keyDownHandler)
          .on('paste', this.pasteHandler);
    };
    AutoSave.prototype.removeField = function () {
        this.save();
    };

    GOVUK.AutoSave = AutoSave;

})(jQuery, GOVUK);
