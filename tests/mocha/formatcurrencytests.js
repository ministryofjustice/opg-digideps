describe('Autosave Tests', function () {

    var placeholder = $('#placeholder'),
        template = $('#template'),
        firstInput;

    beforeEach(function() {
        placeholder.empty('').append(template.html());
        firstInput = $('#test-input');

        firstInput.on('blur', function (event) {
            GOVUK.formatCurrency(event.target);
        });

    });
    afterEach(function() {
        placeholder.text('');
    });

    describe('formatting', function () {
        it('should insert , in numbers greater than 999', function () {
            firstInput.val('10000');
            validKey(firstInput);
            firstInput.trigger('blur');
            expect(firstInput.val()).to.contain('10,000');
        });
        it('should put in 00 if no decimals are entered', function () {
            firstInput.val('100');
            validKey(firstInput);
            firstInput.trigger('blur');
            expect(firstInput.val()).to.equal('100.00');
        });
        it('should append a 0 if only a single place decimal is entered', function () {
            firstInput.val('100.1');
            validKey(firstInput);
            firstInput.trigger('blur');
            expect(firstInput.val()).to.equal('100.10');
        });
        it('should round down to 2 decimal places', function () {
            firstInput.val('100.223');
            validKey(firstInput);
            firstInput.trigger('blur');
            expect(firstInput.val()).to.equal('100.22');
        });
        it('should round up to 2 decimal places', function () {
            firstInput.val('100.229');
            validKey(firstInput);
            firstInput.trigger('blur');
            expect(firstInput.val()).to.equal('100.23');
        });
        it('should handle negative numbers', function () {
            firstInput.val('-100.23');
            validKey(firstInput);
            firstInput.trigger('blur');
            expect(firstInput.val()).to.equal('-100.23');
        });
    });

    function validKey(element) {
        var e = jQuery.Event("keypress");
        e.which = 49;  // number 1
        $(element).trigger(e);
    }

});
