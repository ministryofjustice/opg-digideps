/* globals jQuery */
var opg = opg || {};

(function ($, opg) {
    
    opg.GrandTotal = function (options) {
     
        var _this = this;
        
        this.moneyInElement = $(options.moneyIn);
        this.moneyOutElement = $(options.moneyOut);
        this.grandTotalElement = $(options.grandTotal);
        this.startBalance = options.startBalance;
        this.balance = 0.00;
        this.currency = options.currency;
        
        this.moneyInElement.on('totalChange', function () {
            _this.change();
        });
        this.moneyOutElement.on('totalChange', function () {
           _this.change(); 
        });
        
    };
    
    opg.GrandTotal.prototype.change = function () {
               
        var moneyIn = this.moneyInElement.attr('data-total') ? parseFloat(this.moneyInElement.attr('data-total')) : 0.00,
            moneyOut = this.moneyOutElement.attr('data-total') ? parseFloat(this.moneyOutElement.attr('data-total')) : 0.00;
        
        this.balance = this.startBalance + moneyIn - moneyOut;
        this.displayTotal();
    };

    opg.GrandTotal.prototype.displayTotal = function () {
        var negative = this.balance < 0? '- ' : '';
        
        this.grandTotalElement.text(negative + this.currency + Math.abs(this.balance).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,'));
    };
    
})(jQuery, opg);
