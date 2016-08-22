/**
 * Calculate the total
 */
/* globals jQuery */
var opg = opg || {};

(function ($, opg) {
    
    opg.TotalCalculator = function (options) {
        var _this = this;

        _this.wrapper = $(options.wrapperSelector);
        _this.amountSelector = options.amountSelector;
        _this.totalElement = $(options.totalSelector);

        _this.wrapper.on('change keyup', _this.amountSelector, function(){
            _this.updateTotal();
        });

        _this.updateTotal();
    };

    opg.TotalCalculator.prototype.updateTotal = function() {
        var total = 0;
        this.wrapper.find(this.amountSelector).each(function(i, e) {
            var eVal = parseFloat($(e).val().replace(/,/g , ""));
            if (!isNaN(eVal) && eVal > 0.01) {
                total += eVal;
            }
        });
        this.totalElement.html(total.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,'));
    };

})(jQuery, opg);
