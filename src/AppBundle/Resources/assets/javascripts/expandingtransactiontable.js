(function () {
    "use strict";

    var root = this,
        $ = root.jQuery;
    
    if (typeof GOVUK === 'undefined') { root.GOVUK = {}; }
    
    var ExpandingTransactionTable = function(element) {

        this.container = $(element);
        this.inputs = this.container.find('.transaction-value');
        this.subTotals = this.container.find('.summary .sub-total .value');
        this.grandTotal = this.container.find('.grand-total .value');
        this.form = this.container.find('form');
        
        this.addElementLevelEvents();
        this.closeAll();
        this.setInitialDescriptionVisibility();
    };
    
    ExpandingTransactionTable.prototype.addElementLevelEvents = function () {
        this.clickHandler = this.getSummaryClickHandler();
        this.totalChangeHandler = this.getTotalChangeHandler();
        this.formSubmitHandler = this.getFormSubmitHandler();
        
        $('.summary', this.element).on('click', this.clickHandler);
        this.inputs.on('keyup input paste', this.totalChangeHandler);
        this.form.on('submit', this.formSubmitHandler);
    };
    ExpandingTransactionTable.prototype.getSummaryClickHandler = function () {
        return function (e) {
            this.handleSummaryClick($(e.target));
        }.bind(this);
    };
    ExpandingTransactionTable.prototype.getTotalChangeHandler = function () {
        return function (e) {
            var $target = $(e.target);
            
            this.handleTotalChange($target);
            this.shouldDisplayDescription($target.closest('.transaction'));
        }.bind(this);
    };
    ExpandingTransactionTable.prototype.getFormSubmitHandler = function () {
        return function (e) {
            var $target = $(e.target);
            this.handleFormSubmit($target);
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

        $('.transaction-value', section).each(function (index, element) {
            var value = parseFloat(element.value.replace(/,/g , ""));
            if (!isNaN(value)) {
                total += value;
            }
        });
        
        $('.sub-total .value', section).text(total.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,'));

        this.updateGrandTotal();

    };
    ExpandingTransactionTable.prototype.handleFormSubmit = function () {
        var clearDescription = this.clearDescription;
        $('.transaction', this.container).each(function (index, element) {
            clearDescription(element);
        });           
    };
    ExpandingTransactionTable.prototype.updateGrandTotal = function () {
        var total = 0;

        this.subTotals.each(function (index, element) {
            var value = parseFloat(element.innerHTML.replace(/,/g , ""));
            if (!isNaN(value)) {
                total += value;
            }
        });
        
        this.grandTotal.text(total.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,'));
    };
    ExpandingTransactionTable.prototype.closeAll = function () {
        $('.open', this.container).removeClass('open');
    };
    ExpandingTransactionTable.prototype.setInitialDescriptionVisibility = function () {
    
        var shouldDisplayDescription = this.shouldDisplayDescription;
        
        $('.transaction-more-details', this.container).each(function (index, element) {
            
            var transaction = $(element).closest('.transaction');
            shouldDisplayDescription(transaction);
            
        });
        
        
    };
    ExpandingTransactionTable.prototype.shouldDisplayDescription = function (transaction) {
        var valueElement = $('.transaction-value', transaction);
        var value = parseFloat(valueElement.val().replace(/,/g , ""));

        if (isNaN(value) || value === 0) {
            transaction.addClass('hide-description');
        } else {
            transaction.removeClass('hide-description');
        }
    };
    ExpandingTransactionTable.prototype.clearDescription = function (transaction) {
        var valueElement = $('.transaction-value', transaction);
        var value = parseFloat(valueElement.val().replace(/,/g , ""));

        if (isNaN(value) || value === 0) {
            $('.transaction-more-details', transaction).val('');
        }
    };
    root.GOVUK.ExpandingTransactionTable = ExpandingTransactionTable;

}).call(this);
