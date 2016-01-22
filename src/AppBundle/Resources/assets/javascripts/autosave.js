/*jshint browser: true */
(function () {
    "use strict";

    
    
    var root = this,
        $ = root.jQuery,
        body = $('body');
    
    if (typeof GOVUK === 'undefined') { root.GOVUK = {}; }

    var AutoSave = function(options) {

        this.form = $(options.form);
        this.statusElement = $(options.statusElement);
        this.url = options.url;
        this.saved = true;

        this.addEventHandlers();
        
    };
    
    AutoSave.prototype.addEventHandlers = function () {
        this.blurHandler = this.getBlurHandler();
        this.submitHandler = this.getSubmitHandler();
        this.changedHandler = this.getChangedHandler();
        this.form.on('submit', this.submitHandler);
        this.form.find('input,textarea')
            .on('blur', this.blurHandler)
            .on('keyup', this.changedHandler)
            .on('paste', this.changedHandler);
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
    AutoSave.prototype.getChangedHandler = function () {
        return function () {
            this.saved = false;
        }.bind(this);  
    };
    
    AutoSave.prototype.save = function () {
        
        var data = this.form.serialize();
        var saveDone = this.handleSaveDone.bind(this);
        var saveFail = this.handleSaveError.bind(this);
        
        $.ajax({
            type: 'PUT',
            url: this.url,
            data: data,
            done: function(data) {
                saveDone(data);
            },
            fail: function(data) {
                saveFail(data);

            }
        });
 
    };
    
    AutoSave.prototype.showFieldErrors = function (errors) {
        console.log(errors);

        // for each one use the id to get the input value

        // now get a reference to it's parent

        // get a reference to the label.form-label below it

    };
    
    AutoSave.prototype.handleSaveDone = function (data) {
        this.saved = true;
        //console.log('done');
        //console.log(data);
    };
    
    AutoSave.prototype.handleSaveError = function (data) {
        console.log(data);
        if (data.errors.errorCode === 1001 && data.errors.hasOwnProperty('fields')) {
            //showFieldErrors(data.errors.fields);
        } else {
            //showGeneralError(data.errors.description);
        }
    };
    
    root.GOVUK.AutoSave = AutoSave;
    
}).call(this);
