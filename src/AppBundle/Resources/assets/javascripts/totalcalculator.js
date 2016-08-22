/**
 * Calculate the total
 */
/* globals jQuery */
var opg = opg || {};

(function ($, opg) {
    
    opg.TotalCalculator = function (wrapper, elementsSelector, totalElementSelector) {

        var _this = this;

        _this.wrapper = $(wrapper);
        _this.elementsSelector = elementsSelector;
        _this.totalElementSelector = totalElementSelector;

        _this.wrapper.on('change keyup', this.elementsSelector, function(){
            _this.updateTotal();
        });

        _this.updateTotal();
    };

    opg.TotalCalculator.prototype.updateTotal = function() {
        var total = 0;
        this.wrapper.find(this.elementsSelector).each(function(i, e) {
            var eVal = parseFloat($(e).val().replace(/,/g , ""));
            if (!isNaN(eVal) && eVal > 0.01) {
                total += eVal;
            }
        });
        this.wrapper.find(this.totalElementSelector).html(total.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,'));
    };

})(jQuery, opg);
