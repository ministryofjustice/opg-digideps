/* globals jQuery */
(function () {
    "use strict";

    var root = this,
        $ = root.jQuery;
    
    if (typeof GOVUK === 'undefined') { root.GOVUK = {}; }
    
    var ExpandingTransactionTable = function(element) {

        this.container = $(element);
        this.inputs = this.container.find('input');
        this.subTotals = this.container.find('.summary .sub-total .value');
        this.grandTotal = this.container.find('.grand-total .value');
        
        this.addElementLevelEvents();
        this.closeAll();
    };
    
    ExpandingTransactionTable.prototype.addElementLevelEvents = function () {
        this.clickHandler = this.getSummaryClickHandler();
        $('.summary', this.element).on('click', this.clickHandler);
        
        this.totalChangeHandler = this.getTotalChangeHandler();
        this.inputs.on('keyup input paste', this.totalChangeHandler);
    };
    ExpandingTransactionTable.prototype.getSummaryClickHandler = function () {
        return function (e) {
            this.handleSummaryClick($(e.target));
        }.bind(this);
    };
    ExpandingTransactionTable.prototype.getTotalChangeHandler = function () {
        return function (e) {
            this.handleTotalChange($(e.target));
        }.bind(this);
    };

    
    ExpandingTransactionTable.prototype.handleSummaryClick = function (target) {

        var section = target.closest('.section');

        // if this one is open, close it
        if (section.hasClass('open')) {
            section.removeClass('open');
        } else {
            this.closeAll();
            section.addClass('open');
        }

    };
    ExpandingTransactionTable.prototype.handleTotalChange = function (target) {

        var section = target.closest('.section');
        var total = 0.00;

        $('input.form-control', section).each(function (index, element) {
            var value = parseFloat(element.value.replace(/,/g , ""));
            if (!isNaN(value)) {
                total += value;
            }
        });
        
        $('.sub-total .value', section).text(formatNumber(total));

        this.updateGrandTotal();

    };
    
    function formatNumber(number) {
        var toFixed = parseFloat(number).toFixed(2);
        var digits = toFixed.substr(toFixed.length - 3);
        var leftSide = toFixed.substr(0, toFixed.length -3);
        var formattedLeft = parseInt(leftSide).toLocaleString();
        return formattedLeft + digits;
    }
    
    ExpandingTransactionTable.prototype.updateGrandTotal = function() {
        var total = 0;

        this.subTotals.each(function (index, element) {
            var value = parseFloat(element.innerHTML.replace(/,/g , ""));
            if (!isNaN(value)) {
                total += value;
            }
        });
        
        this.grandTotal.text(formatNumber(total));
    };    
    
    ExpandingTransactionTable.prototype.closeAll = function() {
        $('.open', this.container).removeClass('open');
    };

    
    root.GOVUK.ExpandingTransactionTable = ExpandingTransactionTable;

}).call(this);
