/* globals describe:true, beforeEach:true, afterEach:true, it:true */
describe('Auto total tests', function () {

    var placeholder = $('#placeholder'),
        markup = '<div id="test-wrapper">' +
            '<div id="top-sub-total" class="sub-total">+ £0.00</div>' +
            '<div class="form-group form-group-combo-input">'+
            '<label class="form-label  required required" for="transactions_moneyIn_16_amount"></label>' +
            '<span class="input-group-prefix">£</span>'+
            '<input type="text" id="transactions_moneyIn_16_amount" name="transactions[moneyIn][16][amount]" required="required" class=" form-control form-control__number">'+
            '</div>' +
            '<div class="form-group form-group-combo-input">'+
            '<label class="form-label  required required" for="transactions_moneyIn_17_amount"></label>' +
            '<span class="input-group-prefix">£</span>'+
            '<input type="text" id="transactions_moneyIn_17_amount" name="transactions[moneyIn][17][amount]" required="required" class=" form-control form-control__number">'+
            '</div>' +
            '<div class="form-group form-group-combo-input">'+
            '<label class="form-label  required required" for="transactions_moneyIn_18_amount"></label>' +
            '<span class="input-group-prefix">£</span>'+
            '<input type="text" id="transactions_moneyIn_18_amount" name="transactions[moneyIn][18][amount]" required="required" class=" form-control form-control__number">'+
            '</div>' +
            '<div id="bottom-sub-total" class="sub-total">+ £0.00</div>' +
            '</div>',
        accountAutoTotal,
        topSubTotal, bottomSubTotal;


    beforeEach(function () {
        placeholder.empty().append($(markup));
        accountAutoTotal = new opg.AccountAutoTotal({
            valueSelector: '#test-wrapper .form-control__number',
            totalSelector: '#test-wrapper .sub-total',
            valuePrefix: '+',
            currency: '£'
        });
        topSubTotal = $('#top-sub-total');
        bottomSubTotal = $('#bottom-sub-total');
    });
    afterEach(function() {
        placeholder.empty();
    });

    describe('Auto Total', function () {
        
        it('should initially show the original total', function () {
            expect(topSubTotal.text()).to.equal('+ £0.00');
            expect(bottomSubTotal.text()).to.equal('+ £0.00');
        });
        it('Should add up two simple numbers', function () {
            setTransaction(16,"1.00");
            setTransaction(17,"1.00");
            checkTotal("2.00");
        });
        it('Handle going from a number to a blank.', function () {
            setTransaction(16,"1.00");
            setTransaction(17,"1.00");
            checkTotal("2.00");
            setTransaction(17,"");
            checkTotal("1.00");
        });
        it('Handle going from a number to another number.', function () {
            setTransaction(16,"1.00");
            setTransaction(17,"1.00");
            checkTotal("2.00");
            setTransaction(17,"2.00");
            checkTotal("3.00");
        });
        it('Should handle commas', function () {
            setTransaction(16,"1,000.00");
            setTransaction(17,"1,000.00");
            checkTotal("2,000.00");
        });
        it('Should ignore non numbers', function () {
            setTransaction(16,"1.00");
            setTransaction(17,"fred");
            setTransaction(18,"1.00");
            checkTotal("2.00");
        });
        it('Should trigger an event on the total fields when they are changed.', function(done) {

            topSubTotal.on('totalChange', function(event) {
                expect(event.total).to.equal(1);
                done();
            });
            
            setTransaction(16,"1.00");
            
        });
        it('Should set a data attribute with the total', function () {
            setTransaction(16,"1.00");
            setTransaction(17,"1.00");
            expect(topSubTotal.attr('data-total')).to.equal('2');
        });
        describe('Handles decimal places', function () {
            it('handles numbers with 2 decimals', function () {
                setTransaction(16,"1.00");
                setTransaction(17,"1.00");
                checkTotal("2.00");
            });
            it('handles numbers with 0 decimals', function () {
                setTransaction(16,"1");
                setTransaction(17,"1.00");
                checkTotal("2.00");
            });
            it('add up pounds AND pence', function () {
                setTransaction(16,"1.25");
                setTransaction(17,"1.30");
                checkTotal("2.55");
            });
        });
        describe('Formatting', function () {
            it('should use the correct currency symbol', function () {
                setTransaction(16,"1.00");
                expect(topSubTotal.text()).to.contain('£');
            });
            it('should use the correct prefix', function () {
                setTransaction(16,"1.00");
                expect(topSubTotal.text()).to.contain('+');
            });
            it('should insert , in long numbers', function () {
                setTransaction(16,"1000.00");
                setTransaction(17,"1000.00");
                checkTotal("2,000.00");
            });
        });        
    });
    
    function setTransaction(transactionNumber, value) {
        var field = $('#transactions_moneyIn_' + transactionNumber + '_amount');
        field.val(value);
        field.trigger('keyup');
    }
    function checkTotal(expected) {
        expect(topSubTotal.text()).to.contain(expected);
        expect(bottomSubTotal.text()).to.contain(expected);
    }
    
});
