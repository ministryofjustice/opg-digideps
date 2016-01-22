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
    
    AutoSave.prototype.addEventHandlers = function () {
        this.blurHandler = this.getBlurHandler();
        this.submitHandler = this.getSubmitHandler();
        this.leaveHandler = this.getLeaveHandler();
        
        this.form.on('submit', this.submitHandler);
        this.form.find(['input']).on('blur', this.blurHandler);
        //$(window).on("beforeunload", this.leaveHandler);
    };
    
    AutoSave.prototype.getBlurHandler = function () {
        return function (e) {
            e.preventDefault();
            this.save();
            return true;
        }.bind(this);
    };
    AutoSave.prototype.getSubmitHandler = function () {
        return function (e) {
            e.preventDefault();
            this.save();
            // redirect to desired location
            return false;
        }.bind(this);
    };
    AutoSave.prototype.getLeaveHandler = function () {
        return function (e) {
            e.preventDefault();
            this.save();
            return true;
        }.bind(this);
    };
    
    AutoSave.prototype.handleChange = function (target) {
        // set status flag to say data has changed.
    };
    
    AutoSave.prototype.save = function (done) {
        
        var data = this.form.serialize();
        
        $.ajax({
            type: 'PUT',
            url: this.url,
            data: data,
            done: function(data) {
                this.handleSaveDone(data);
                done();
            },
            fail: function(data) {
               this.handleSaveError(data);
                done();
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
        console.log('done');
        console.log(data);
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
