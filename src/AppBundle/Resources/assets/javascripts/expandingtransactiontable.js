/*jshint browser: true */
(function () {
    "use strict";

    var root = this,
        $ = root.jQuery;

    if (typeof GOVUK === 'undefined') { root.GOVUK = {}; }

    var ExpandingTransactionTable = function(element) {

        this.container = $(element);
        this.inputs = this.container.find('.transaction-value').not(".exclude-total");
        this.subTotals = this.container.find('.summary .sub-total .value');
        this.grandTotal = this.container.find('.grand-total .value');

        this.handleSummaryClick = this.handleSummaryClick.bind(this);
        this.totalChangeHandler = this.totalChangeHandler.bind(this);
        this.handleFormSubmit = this.handleFormSubmit.bind(this);

        $('.summary', this.element).on('click', this.handleSummaryClick);
        this.inputs.on('keyup input paste recalc', this.handleInputChange);
        $(window).on('TRANSACTION_TOTAL_CHANGE', this.totalChangeHandler);
        this.container.on('submit', this.formSubmitHandler);

        this.closeAll();
        this.setInitialDescriptionVisibility();
        this.openSectionsWithErrors();
    };

    ExpandingTransactionTable.prototype.handleInputChange = function (event) {
        $(window).trigger({type:'TRANSACTION_TOTAL_CHANGE', input: event.target});
    };
    ExpandingTransactionTable.prototype.totalChangeHandler = function (event) {
        var $target = $(event.input);
        this.handleTotalChange($target);
        this.shouldDisplayDescription($target.closest('.transaction'));
    };
    ExpandingTransactionTable.prototype.handleSummaryClick = function (event) {
        var target = $(event.target);
        var section = target.closest('.section');

        // if this one is open, close it
        if (section.hasClass('open')) {
            section.removeClass('open');
            section.addClass('closed');
        } else {
            section.addClass('open');
            section.removeClass('closed');
        }

    };
    ExpandingTransactionTable.prototype.handleTotalChange = function (target) {

        var section = target.closest('.section');
        var total = 0.00;

        $('.transaction-value', section).not('.exclude-total').each(function (index, element) {
            var value = parseFloat(element.value.replace(/,/g , ""));
            if (!isNaN(value)) {
                total += value;
            }
        });

        $('.sub-total .value', section).text(total.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,'));

        this.updateGrandTotal();

    };
    ExpandingTransactionTable.prototype.handleFormSubmit = function (event) {
        event.preventDefault();
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
        $('.section', this.container).removeClass('open').addClass('closed');
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
        var hasError = $('.error', transaction).length > 0;

        if (isNaN(value) && !hasError || value === 0 && !hasError) {
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
    ExpandingTransactionTable.prototype.openSectionsWithErrors = function () {
        $('.error', this.container).each(function (index, element) {
           $(element).closest('.section').addClass('open').removeClass('closed');
        });
    };

    root.GOVUK.ExpandingTransactionTable = ExpandingTransactionTable;

}).call(this);
