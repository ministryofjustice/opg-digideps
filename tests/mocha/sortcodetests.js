describe('Sort Code Tests', function () {

    var placeholder = $('#placeholder'),
        markup = '<div id="sort-code-wrapper" class="form-group">' +
                    '<fieldset>' +
                        '<legend><span class="form-label">Branch Sort Code</span></legend>' +
                        '<div class="form-sort-code">' +
                            '<ul class="errors"></ul>' + 
                            '<div class="form-group">' +
                                '<label for="account_sortCode_sort_code_part_1" class="visuallyhidden required">Sort Code part 1</label>' +
                                '<input type="text" id="account_sortCode_sort_code_part_1" name="account[sortCode][sort_code_part_1]" required="required" maxlength="2" class="sort-code-part form-control" />' +
                            '</div>' +
                            '<div class="separator">-</div>' +
                            '<div class="form-group">' +
                                '<label for="account_sortCode_sort_code_part_2" class="visuallyhidden required">Sort Code part 2</label>' +
                                '<input type="text" id="account_sortCode_sort_code_part_2" name="account[sortCode][sort_code_part_2]" required="required" maxlength="2" class="sort-code-part form-control" />' +
                            '</div>' +
                            '<div class="separator">-</div>' +
                            '<div class="form-group">' +
                                '<label for="account_sortCode_sort_code_part_3" class="visuallyhidden required">Sort Code part 2</label>' +
                                '<input type="text" id="account_sortCode_sort_code_part_3" name="account[sortCode][sort_code_part_3]" required="required" maxlength="2" class="sort-code-part form-control" />' +
                            '</div>' +
                        '</div>' +
                    '</fieldset>' +
                '</div>' +
                '<input id="other-field" name="otherField" text="text"/>';
    var field1, field2, field3;

    beforeEach(function() {
        placeholder.empty('').append($(markup));
        field1 = $('#account_sortCode_sort_code_part_1');
        field2 = $('#account_sortCode_sort_code_part_2');
        field3 = $('#account_sortCode_sort_code_part_3');
        opg.SortCodeValidate('#sort-code-wrapper');
    });
    afterEach(function() {
        placeholder.empty();
    });
    
    describe('Navigate between fields', function () {
        it('should automatically move from the first to the second when you enter 2 chars', function () {
            enterValueInField(field1,'11');
            expect(fieldIsActive(field2)).to.be.true;
        });
        it('should automatically move from the second to the third when you enter 2 characters', function () {
            enterValueInField(field2,'11');
            expect(fieldIsActive(field3)).to.be.true;
        });
        it('should not move from the third when you enter 2 characters', function () {
            enterValueInField(field3,'11');
            expect(fieldIsActive(field3)).to.be.true;
        });
        it('should not move from the first to the second when you only enter 1 character', function () {
            enterValueInField(field1,'1');
            expect(fieldIsActive(field1)).to.be.true;
        });
        it('should not move from the second to the third when you only enter 1 character', function () {
            enterValueInField(field2,'1');
            expect(fieldIsActive(field2)).to.be.true;
        });
    });
    describe('Validate field values', function () {
        describe('mark fields invalid', function () {
            it('should mark the first field invalid for none numeric values', function () {
                enterValueInField(field1,'dd');
                field1.trigger('blur');
                expect(fieldMarkedInvalid(field1)).to.be.true;
            });
            it('should mark the second field invalid for none numeric values', function () {
                enterValueInField(field2,'dd');
                expect(fieldMarkedInvalid(field2)).to.be.true;
            });
            it('should mark the third field invalid for none numeric values', function () {
                enterValueInField(field3,'dd');
                field3.blur();
                expect(fieldMarkedInvalid(field3)).to.be.true;
            });
            it('should mark the first field as bad if the value is too short', function () {
                enterValueInField(field1,'1');
                field1.blur();
                expect(fieldMarkedInvalid(field1)).to.be.true;
            });
            it('should mark the second field as bad if the value is too short', function () {
                enterValueInField(field2,'1');
                field2.blur();
                expect(fieldMarkedInvalid(field2)).to.be.true;
            });
            it('should mark the third field as bad if the value is too short', function () {
                enterValueInField(field3,'1');
                field3.blur();
                expect(fieldMarkedInvalid(field3)).to.be.true;
            });
            it('should mark a field as invalid when no value is entered', function () {
                enterValueInField(field1, '');
                field1.blur();
                expect(fieldMarkedInvalid(field1)).to.be.true;
            });
            it('should clear the error marker off a field when it is corrected.', function () {
                enterValueInField(field1,'1');
                field1.blur();
                enterValueInField(field1,'11');
                field1.blur();
            });
        });
        describe('show error message', function () {
            it('should tell the user that they entered a none numeric value in field 1 if all chars', function () {
                enterValueInField(field1,'dd');
                field1.blur();
                expect(errorMessage()).to.contain('Sort code must be numeric');
            });
            it('should tell the user that they entered a none numeric value in field 1 if 1 char', function () {
                enterValueInField(field1,'d');
                field1.blur();
                expect(errorMessage()).to.contain('Sort code must be numeric');
            });
            it('should tell the user that they entered a none numeric value in field 1 if mixed chars and numbers', function () {
                enterValueInField(field1,'1d');
                field1.blur();
                expect(errorMessage()).to.contain('Sort code must be numeric');
            });
            it('should show an error and then remove it when you fix it', function () {
                enterValueInField(field1, 'dd');
                field1.blur();
                enterValueInField(field2, '11');
                field2.blur();
                enterValueInField(field1, '11');
            });
        });
        describe('only validate when you need to', function () {
            it('should not warn me that part 3 is invalid until the user puts a value into it', function () {
                enterValueInField(field1,'dd');
                expect(fieldMarkedInvalid(field3)).to.be.false;
            });
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