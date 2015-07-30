describe('Date validation tests', function () {

    var placeholder = $('#placeholder'),
        markup = '<div class="form-group date-wrapper">' +
                    '<fieldset>' +
                        '<legend class="form-label">Add the opening balance date for this account</legend>' +
                        '<div class="form-date">' +

                            '<p class="form-hint">(DD/MM/YYYY)</p>' +

                            '<div class="form-group form-group-day">' +
                                '<label for="account_openingDate_day" class="visuallyhidden required">Day</label>' +
                                '<input type="text" id="account_openingDate_day" name="account[openingDate][day]" required="required" class="form-control" pattern="[0-9]" maxlength="2" value="" />' +
                            '</div>' +

                            '<div class="form-group form-group-month">' +
                                '<label for="account_openingDate_month" class="visuallyhidden required">Month</label>' +
                                '<input type="text" id="account_openingDate_month" name="account[openingDate][month]" required="required" class="form-control" pattern="[0-9]" maxlength="2" value="" />' +
                            '</div>' +

                            '<div class="form-group form-group-year">' +
                                '<label for="account_openingDate_year" class="visuallyhidden required">Year</label>' +
                                '<input type="text" id="account_openingDate_year" name="account[openingDate][year]" required="required" class="form-control" pattern="[0-9]" maxlength="4" />' +
                            '</div>' +
                        '</div>' +
                    '</fieldset>' +
                '</div>',
        dayField, monthField, yearField,
        validator,
        wrapper;
    
    beforeEach(function () {
        placeholder.empty().append($(markup));
        wrapper = $('.date-wrapper').first();
        validator = new opg.DateValidate(wrapper[0]);
        dayField = wrapper.find('#account_openingDate_day');
        monthField = wrapper.find('#account_openingDate_month');
        yearField = wrapper.find('#account_openingDate_year');
    });
    afterEach(function() {
        //placeholder.empty();
    });
    
    describe('Navigate between fields', function () {
        it('should automatically move from the first to the second when you enter 2 chars', function () {
            enterValueInField(dayField,'11');
            expect(fieldIsActive(monthField)).to.be.true;
        });
        it('should automatically move from the second to the third when you enter 2 characters', function () {
            enterValueInField(monthField,'11');
            expect(fieldIsActive(yearField)).to.be.true;
        });
        it('should not move from the third when you enter 4 characters', function () {
            enterValueInField(yearField,'2000');
            expect(fieldIsActive(yearField)).to.be.true;
        });
        it('should not move from the first to the second when you only enter 1 character', function () {
            enterValueInField(dayField,'1');
            expect(fieldIsActive(dayField)).to.be.true;
        });
        it('should not move from the second to the third when you only enter 1 character', function () {
            enterValueInField(monthField,'1');
            expect(fieldIsActive(monthField)).to.be.true;
        });
    });
    describe('Validate on blur', function () {
        var validateStub;
        
        beforeEach(function () {
            validateStub = sinon.spy(opg.DateValidate.prototype, "validate");
        });
        afterEach(function () {
            opg.DateValidate.prototype.validate.restore();
        });
        
        it('should call the validator on blur', function () {
            dayField.trigger('blur');
            expect(validateStub.callCount).to.equal(1);
        });
        
    });
    describe('Pad on blur', function () {
        it('should pad a day with a 0 if the value is < 10', function (done) {
            dayField.val('1')
            dayField.trigger('blur');
            expect(dayField.val()).to.equal("01");
            done();
        });
    });
    describe('Validate on maxlength', function () {
        var validateStub;
        
        beforeEach(function () {
            validateStub = sinon.spy(opg.DateValidate.prototype, "validate");
        });
        afterEach(function () {
            opg.DateValidate.prototype.validate.restore();
        });

        it('should not call the validator for a single character on day', function () {
            dayField.val('1').trigger('input');
            expect(validateStub.callCount).to.equal(0);
        });
        it('should not call the validator for a single character on month', function () {
            monthField.val('1').trigger('input');
            expect(validateStub.callCount).to.equal(0);
        });
        it('should not call the validator for less than 4 characters on year', function () {
            yearField.val('112').trigger('input');
            expect(validateStub.callCount).to.equal(0);
        });    
        it('should call the for 2 characters on day', function () {
            dayField.val('11').trigger('input');
            expect(validateStub.callCount).to.equal(1);
        });
        it('should call the for 2 characters on month', function () {
            monthField.val('11').trigger('input');
            expect(validateStub.callCount).to.equal(1);
        });
        it('should call the for 4 characters on year', function () {
            yearField.val('2000').trigger('input');
            expect(validateStub.callCount).to.equal(1);
        });        
    });
    describe('Validate the day field', function () {
        it('should not throw an error if the field is empty when you move off it', function () {
            enterValueInField(dayField, '');
            expect(fieldMarkedInvalid(dayField)).to.be.false;
        });
        it('should throw an error if the value if not a number', function () {
            enterValueInField(dayField,'Mo');
            expect(fieldMarkedInvalid(dayField)).to.be.true;
            expect(fieldMarkedInvalid(monthField)).to.be.false;
            expect(fieldMarkedInvalid(yearField)).to.be.false;
        });
        it('should throw an error if the number is < 1', function () {
            dayField.val('0').trigger('blur');
            expect(fieldMarkedInvalid(dayField)).to.be.true;
        });
        it('should throw an error if you put a number > 31 and there is no month', function () {
            dayField.val('32').trigger('blur');
            expect(fieldMarkedInvalid(dayField)).to.be.true;
        });
        it('should accept a number between 0 and 31 when you have not specified a month', function () {
            dayField.val('25').trigger('blur');
            expect(fieldMarkedInvalid(dayField)).to.be.false;
        });
        it('should throw an error if the value is 31 and the month has 30 days', function () {
            dayField.val('31').trigger('blur');
            monthField.val('6').trigger('blur');
            expect(fieldMarkedInvalid(dayField)).to.be.true;
        });
        it('should not throw an error if the value is 31 and the month has 31 days', function () {
            dayField.val('31').trigger('blur');
            monthField.val('1').trigger('blur');
            expect(fieldMarkedInvalid(dayField)).to.be.false;
        });
        it('should throw an error if you enter a number > 29 and the month is currently 2', function () {
            dayField.val('30').trigger('blur');
            monthField.val('2').trigger('blur');
            expect(fieldMarkedInvalid(dayField)).to.be.true;
        });
        it('should throw an error if you enter 29 and the year entered is not a leap year', function () {
            dayField.val('29').trigger('blur');
            monthField.val('2').trigger('blur');
            yearField.val('2015').trigger('blur');
            expect(fieldMarkedInvalid(dayField)).to.be.true;
        });
        it('should accept a value of 29 if the year entered is a leap year', function () {
            dayField.val('29').trigger('blur');
            monthField.val('2').trigger('blur');
            yearField.val('2016').trigger('blur');
            expect(fieldMarkedInvalid(dayField)).to.be.false;
        });
        it('should throw an error if you enter 30 as the value and then change the month to 2', function () {
            dayField.val('30').trigger('blur');
            monthField.val('3').trigger('blur');
            yearField.val('2015').trigger('blur');
            expect(fieldMarkedInvalid(dayField)).to.be.false;
            monthField.val('2').trigger('blur');
            expect(fieldMarkedInvalid(dayField)).to.be.true;
        });
        it('should throw an error if you enter 29 as the value then change it to a none leap year', function () {
            dayField.val('29').trigger('blur');
            monthField.val('2').trigger('blur');
            yearField.val('2016').trigger('blur');
            expect(fieldMarkedInvalid(dayField)).to.be.false;
            yearField.val('2015').trigger('blur');
            expect(fieldMarkedInvalid(dayField)).to.be.true;         
        });
        it('should clear an error if you enter a bad value and replace with a good one', function () {
            dayField.val('40').trigger('blur');
            expect(fieldMarkedInvalid(dayField)).to.be.true;
            dayField.val('20').trigger('blur');
            expect(fieldMarkedInvalid(dayField)).to.be.false;
        });
        it('should clear the outer error when clearing errors', function (){
            dayField.val('0').trigger('blur');
            expect(sectionMarkedInvalid()).to.be.true;
            dayField.val('1').trigger('blur');
            expect(sectionMarkedInvalid()).to.be.false;
        });
    });
    describe('Validate the month field', function () {
        it('should not throw an error if the field is empty when you move off it', function () {
            monthField.val('').trigger('blur');
            expect(fieldMarkedInvalid(monthField)).to.be.false;
        });
        it('should throw an error if the value if not a number', function () {
            monthField.val('Ja').trigger('blur');
            expect(fieldMarkedInvalid(monthField)).to.be.true;
        });
        it('should throw an error if the number < 1', function () {
            monthField.val('0').trigger('blur');
            expect(fieldMarkedInvalid(monthField)).to.be.true;
        });
        it('should throw an error if you put a number > 12', function () {
            monthField.val('13').trigger('blur');
            expect(fieldMarkedInvalid(monthField)).to.be.true;
        });
        it('should not throw an error for a valid month', function () {
            monthField.val('11').trigger('blur');
            expect(fieldMarkedInvalid(monthField)).to.be.false;
        });
        it('should clear an error if you enter a bad value and replace with a good one', function () {
            monthField.val('13').trigger('blur');
            expect(fieldMarkedInvalid(monthField)).to.be.true;
            monthField.val('11').trigger('blur');
            expect(fieldMarkedInvalid(monthField)).to.be.false;
        });
    });
    describe('Validate the year field', function () {
        it('should not throw an error if the field is empty when you move off it', function() {
            yearField.val('').trigger('blur');
            expect(fieldMarkedInvalid(yearField)).to.be.false;
        });
        it('should throw an error if the value if not a number', function() {
            yearField.val('zac').trigger('blur');
            expect(fieldMarkedInvalid(yearField)).to.be.true;
        });
        it('should throw an error if the number < 1', function () {
            yearField.val('0').trigger('blur');
            expect(fieldMarkedInvalid(yearField)).to.be.true;
        });
        it('should throw an error if you put a number < 1800', function () {
            yearField.val('1799').trigger('blur');
            expect(fieldMarkedInvalid(yearField)).to.be.true;
        });
        it('should not throw an error if you put in a valid year', function () {
            yearField.val('2016').trigger('blur');
            expect(fieldMarkedInvalid(yearField)).to.be.false;
        });
        it('should clear an error if you enter a bad value and replace with a good one', function () {
            yearField.val('1799').trigger('blur');
            expect(fieldMarkedInvalid(yearField)).to.be.true;
            yearField.val('2016').trigger('blur');
            expect(fieldMarkedInvalid(yearField)).to.be.false;
        });
    });
    describe('Field Error List', function () {
        it('should add an error section if there is an error and no error container');
        it('should replace the contents of the error section if one already exists');
        it('should delete the error list element if there are no errors');
    });
    
    describe('Support multiple date fields', function() {
        
        var wrappers;
        var wrappera;
        var wrapperb;
        
        var validatora;
        var dayFielda;
        var monthFielda;
        var yearFielda;
        
        var validatorb;
        var dayFieldb;
        var monthFieldb;
        var yearFieldb;
        
        beforeEach(function () {
            
            placeholder.empty().append($(markup));
            placeholder.append($(markup));
            
            wrappers = $('.date-wrapper');
            wrappera = $(wrappers[0]);
            wrapperb = $(wrappers[1]);
            
            validatora = new opg.DateValidate(wrappera);
            dayFielda = wrappera.find('#account_openingDate_day');
            monthFielda = wrappera.find('#account_openingDate_month');
            yearFielda = wrappera.find('#account_openingDate_year');
            
            validatorb = new opg.DateValidate(wrapperb);
            dayFieldb = wrapperb.find('#account_openingDate_day');
            monthFieldb = wrapperb.find('#account_openingDate_month');
            yearFieldb = wrapperb.find('#account_openingDate_year');    
        });
        afterEach(function () {
            //placeholder.empty();
        });
                
        it('should not show an error in the first date when the 2nd is bad', function () {
            
            dayFielda.val('1').trigger('blur');
            monthFielda.val('1').trigger('blur');
            yearFielda.val('2015').trigger('blur');
            
            dayFieldb.val('1').trigger('blur');
            monthFieldb.val('1').trigger('blur');
            yearFieldb.val('fred').trigger('blur');
            
            expect(fieldMarkedInvalid(yearFielda)).to.be.false;
            expect(fieldMarkedInvalid(yearFieldb)).to.be.true;
            
        });
        it('Outer error wrapper should be independent', function (){
            dayFielda.val('0').trigger('blur');
            dayFieldb.val('1').trigger('blur');
            expect(wrappera.hasClass('field-with-errors')).to.be.true;
            expect(wrapperb.hasClass('field-with-errors')).to.be.false;
            
            dayFielda.val('1').trigger('blur');
            expect(wrappera.hasClass('field-with-errors')).to.be.false;
            expect(wrapperb.hasClass('field-with-errors')).to.be.false;
            
            dayFielda.val('1').trigger('blur');
            dayFieldb.val('0').trigger('blur');
            expect(wrappera.hasClass('field-with-errors')).to.be.false;
            expect(wrapperb.hasClass('field-with-errors')).to.be.true;
            
            dayFieldb.val('1').trigger('blur');
            expect(wrappera.hasClass('field-with-errors')).to.be.false;
            expect(wrapperb.hasClass('field-with-errors')).to.be.false;
        });
    });
    
    function fieldIsActive(field) {
        return field[0] === document.activeElement;
    }
    
    function fieldMarkedInvalid(field) {
        return field.parent().hasClass('field-with-errors');
    }
    function sectionMarkedInvalid() {
        return wrapper.hasClass('field-with-errors');
    }
    function enterValueInField(field, value, moveOn) {
        $(field).focus().val(value).trigger('input');
    }
    
    function errorMessage() {
        return placeholder.find('.errors').text();
    }
});