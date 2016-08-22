/**
 * Calculate the total
 */
/* globals jQuery */
var opg = opg || {};

(function ($, opg) {
    
    opg.TotalCalculator = function (elements, totalElement) {

        var _this = this;

        this.elements = elements;
        this.totalElement = totalElement;

        this.elements.on('change keyup', function(){
            _this.updateTotal();
        });

        _this.updateTotal();
    };

    opg.TotalCalculator.prototype.updateTotal = function() {
        var total = 0;
        this.elements.each(function(i, e) {
            var eVal = parseFloat($(e).val().replace(/,/g , ""));
            if (!isNaN(eVal)) {
                total += eVal;
            }
        });
        this.totalElement.html(total.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,'));
    };

})(jQuery, opg);
