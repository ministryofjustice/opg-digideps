var opg = opg || {};

(function ($, opg) {

    opg.AccountAutoTotal = function(options) {
      
        var _this = this;
        
        this.valueElements = $(options.valueSelector);
        this.totalElements = $(options.totalSelector);
        this.valuePrefix = options.valuePrefix;
        this.currency = options.currency;

        this.valueElements.on('keyup', function () {
            _this.updateTotals();
        });
    
    };
    
    opg.AccountAutoTotal.prototype.updateTotals = function () {
      
        var total = getTotal(this.valueElements);
        
        var formattedValue = 
            this.valuePrefix + ' ' +
            this.currency + 
            total.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
        
        this.totalElements
            .text(formattedValue)
            .attr('data-total', total)
            .trigger({
                type:'totalChange',
                total: total
            });
        
    };
    
    
    function getTotal(elements) {
        
        var value,
            cleanString,
            total = 0.00;
        
        elements.each(function(index,item) {
            cleanString = item.value.replace(/[^\d\.\-\ ]/g, '');
            value = parseFloat(cleanString);
            if (!isNaN(value)) {
                total += value;
            }
        });
        
        return total;
        
    }
    
})(jQuery, opg);
