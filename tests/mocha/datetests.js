describe('Date validation tests', function () {

    var placeholder = $('#placeholder'),
        markup = '<div id="date-wrapper" class="form-group ">' +
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
        dayField, monthField, yearField;
    
    beforeEach(function () {
        placeholder.empty().append($(markup));
        opg.dateValidate('#date-wrapper');
        dayField = $('#account_openingDate_day');
        monthField = $('#account_openingDate_month');
        yearField = $('#account_openingDate_year');
    });
    afterEach(function() {
        placeholder.empty();
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


    function fieldIsActive(field) {
        return field[0] === document.activeElement;
    }
    
    function fieldMarkedInvalid(field) {
        return field.parent().hasClass('field-with-errors');
    }
    
    function enterValueInField(field, value) {
        field.focus().val(value).trigger('input');
    }
    
    function errorMessage() {
        return placeholder.find('.errors').text();
    }
});