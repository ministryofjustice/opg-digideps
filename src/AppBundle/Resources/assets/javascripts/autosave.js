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

        this.addEventHandlers();
        
    };
    
    AutoSave.prototype.AddEventHandlers = function () {
        this.changeHandler = this.getChangeHandler();

        this.form.on('submit', this.changeHandler);
        this.form.find(['input']).on('blur', this.changeHandler);
        $(window).on("beforeunload", autosave);
        
        // ?? Does a form field blur when you leave a page????
        
    };
    
    AutoSave.prototype.getChangeHandler = function () {
        return function (e) {
            e.preventDefault();
            this.handleChange($(e.target));
        }.bind(this);
    };
    
    AutoSave.prototype.handleChange = function (target) {
        this.save();
    };
    
    AutoSave.prototype.save = function () {
        
        var data = this.form.serialize();
        
        $.ajax({
            type: 'PUT',
            url: this.url,
            data: data,
            done: this.handleSaveDone,
            fail: this.handleSaveError
        });
    
    };
    
    AutoSave.prototype.showFieldErrors = function (errors) {
        console.log(errors);

        // for each one use the id to get the input value

        // now get a reference to it's parent

        // get a reference to the label.form-label below it

    };
    
    AutoSave.prototype.handleSaveDone = function (data) {
        console.log('done')
        console.log(data);
    }
    
    AutoSave.prototype.handleSaveError = function (data) {
        console.log(data);
        if (data.errors.errorCode === 1001 && data.errors.hasOwnProperty('fields')) {
            showFieldErrors(data.errors.fields);
        } else {
            showGeneralError(data.errors.description);
        }
    };
    
    root.GOVUK.AutoSave = AutoSave;
    
}).call(this);




