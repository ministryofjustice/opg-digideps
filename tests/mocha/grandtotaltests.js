/* globals describe:true, beforeEach:true, afterEach:true, it:true */
describe('Grand Total Tests', function () {

    var placeholder = $('#placeholder'),
        markup = '<div id="test-wrapper">' +
            '<div id="in-sub-total" class="sub-total" data-total="0.00">+ £0.00</div>' +
            '<div id="out-sub-total" class="sub-total" data-total="0.00">+ £0.00</div>' +
            '<div id="grand-total" class="grand-total">£100</div>',
        moneyInElement,moneyOutElement,totalElement

    
    beforeEach(function () {
        placeholder.empty().append($(markup));
        
        new opg.GrandTotal({
            moneyIn: '#in-sub-total',
            moneyOut: '#out-sub-total',
            grandTotal: '#grand-total',
            startBalance: 100,
            currency: '£'
        });
        
        moneyInElement = $('#in-sub-total');
        moneyOutElement = $('#out-sub-total');
        totalElement = $('#grand-total');
        
    });
    
    afterEach(function() {
        placeholder.empty();
    });
    
    describe('Update Total', function () {
    
        it('should add money coming in', function () {
            
            moneyInElement
                .text("+ £1.00")
                .attr('data-total', '1')
                .trigger({
                    type:'totalChange',
                    total: 1
                });
            
            expect(totalElement.text()).to.equal("£101.00");
            
        });
        
        it('should handle multiple changes', function () {

            moneyOutElement
                .text("- £1.00")
                .attr('data-total', '1')
                .trigger({
                    type:'totalChange',
                    total: 1
                });

            expect(totalElement.text()).to.equal("£99.00");

            moneyOutElement
                .text("- £1.00")
                .attr('data-total', '1')
                .trigger({
                    type:'totalChange',
                    total: 1
                });

            expect(totalElement.text()).to.equal("£99.00");
            
        });

        it('should take away money out', function () {

            moneyOutElement
                .text("- £1.00")
                .attr('data-total', '1')
                .trigger({
                    type:'totalChange',
                    total: 1
                });

            expect(totalElement.text()).to.equal("£99.00");

        });
        
        it('should combine totals in and out', function () {
            moneyInElement
                .text("+ £50.00")
                .attr('data-total', '50')
                .trigger({
                    type:'totalChange',
                    total: 50
                });

            moneyOutElement
                .text("- £15.00")
                .attr('data-total', '15')
                .trigger({
                    type:'totalChange',
                    total: 15
                });

            expect(totalElement.text()).to.equal("£135.00");            
        });
        
        it('should handle negative numbers', function () {
            moneyInElement
                .text("+ £50.10")
                .attr('data-total', '50.10')
                .trigger({
                    type:'totalChange',
                    total: 50.10
                });

            moneyOutElement
                .text("- £215.00")
                .attr('data-total', '215')
                .trigger({
                    type:'totalChange',
                    total: 215
                });

            expect(totalElement.text()).to.equal("- £64.90");            
        });
        
    });
    
});
