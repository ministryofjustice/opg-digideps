/* globals jQuery */
var opg = opg || {};

(function ($, opg) {

    opg.expandingTransactionTable = function(element) {

        var container,
            inputs,
            subTotals,
            grandTotal;
        
        function closeAll() {
            $('.open', container).removeClass('open');
        }
        
        function handleClick(event) {
            var target = $(event.currentTarget);
            var targetParent = target.parent();
            
            // if this one is open, close it
            if (targetParent.hasClass('open')) {
                targetParent.removeClass('open');
            } else {
                closeAll();
                targetParent.addClass('open');
            }

        }

        function init(element) {
            container = $(element);
            inputs = container.find('input');
            subTotals = container.find('.summary .sub-total .value');
            grandTotal = container.find('.grand-total .value');
            
            
            $('.summary', element).on('click', handleClick);
            
            inputs.on('keyup input paste', updateSectionTotal);
            closeAll();
        }
        
        function updateSectionTotal(event) {
            
            var target = $(event.target);
            var section = target.closest('.section');
            
            var total = 0.00;
            
            $('input.form-control', section).each(function(index, element) {
                var value = parseFloat(element.value);
                if (!isNaN(value)) {
                    total += value;
                }
            });
            
            $('.sub-total .value', section).text(total.toFixed(2));
            
            updateGrandTotal();
        }
        
        function updateGrandTotal() {
            var total = 0;
            
            subTotals.each(function (index, element) {
                var value = parseFloat($(element).text());
                if (!isNaN(value)) {
                    total += value;
                }
            });
            
            grandTotal.text(total.toFixed(2));
        }
        
        init(element);

    };
    

})(jQuery, opg);
