/* globals jQuery */
var opg = opg || {};



/**
 * Handles Transfer operations
 * Prototype version. Refactor if needed
 */
(function ($, opg) {
    "use strict";

    var Transfers = function (options) {
        this.that = this;
        
        this.statusElement = options.statusElement;
        this.removeAtStart = options.removeAtStart;
        this.showAtStart = options.showAtStart;
        this.wrapper = options.wrapper;
        this.saveEndpoint = options.saveEndpoint;
        this.deleteEndpoint = options.deleteEndpoint;
        this.noTransfersEndpoint = options.noTransfersEndpoint;
        this.renderDeleteButtonAfterEndpointCalled = options.renderDeleteButtonAfterEndpointCalled;
        this.noTransferWrapperSelector = options.noTransferWrapperSelector;
        this.noTransferWrapper = $(this.noTransferWrapperSelector);
        this.thereAreNoRecords = options.thereAreNoRecords;
        this.emptyRow = options.emptyRow;
        this.cardSelectionList = options.cardSelectionList;
        
        this.init();
        this.attachEvents();
        this.attachGlobalEvents();
    };

    Transfers.prototype.isFormValid = function (form) {
        var fromVal = parseInt(form.find('input.account:nth(0)').val());
        var toVal = parseInt(form.find('input.account:nth(1)').val());
        var amountVal = parseFloat(form.find('input[name=amount]').val());
        return fromVal > 0 && toVal > 0 && amountVal > 0 && form.find('input.account').length === 2;
    };

    Transfers.prototype.setStatus = function (text) {
        this.statusElement.text(text);
    };

    Transfers.prototype.init = function () {
        var _this = this.that;

        this.removeAtStart.remove();
        this.showAtStart.show();
    };

    Transfers.prototype.filterCards = function(){
        
    };

    Transfers.prototype.attachEvents = function () {
        var _this = this.that;

        // show card selection
        this.wrapper.on('click', '.card-item.expandable', function (e) {
            e.stopPropagation();
            var el = $(this);
            var cards = $(_this.cardSelectionList.clone());
            
            // find sibling
            var column = el.parents('.column-one-half');
            if (column.next().length === 1) {
                var sibling = column.next();
            } else if (column.prev().length === 1) {
                var sibling = column.prev();
            } else {
                var sibling = null;
            }
            // change colour if card equal to sibling
            if (sibling) {
                var idFromOtherColumn = sibling.find('.card-item').data('id');
                cards.find('.card-item').filter(function(){
                    return parseInt($(this).data('id')) === parseInt(idFromOtherColumn);
                }).find('.card').css('background-color', 'red');
            }
            
            el.parent('.card-list').html(cards.html());
        });

        // selecting one from list
        this.wrapper.on('click', '.card-item.not-expandable', function (e) {
            _this.setStatus('');
            var cardsList = $(this).parent('ul.card-list');
            var selectedCard = $(this);
            var form = $(this).parents('form');
            
            // remove cards except the selected one
            cardsList.find('li').filter(function() {
                return parseInt($(this).data('id')) !== parseInt(selectedCard.data('id'));
            }).remove();
            // re-add expandable class for future clicks
            cardsList.find('li.not-expandable').addClass('expandable');
            
            _this.saveTransfer(form);
        });

        // when editable fields change
        this.wrapper.on('change', 'input.balance', function (e) {
            _this.setStatus('');
            var form = $(e.target).parents('form');
            _this.saveTransfer(form);
        });


        // noTransfers checkbox click
        this.wrapper.on('click', _this.noTransferWrapperSelector + ' input[type=checkbox]', function () {
            _this.setStatus('');
            var form = $(this).parents('form');

            $.post(_this.noTransferWrapperSelector, form.serialize());
        });

        // on delete
        this.wrapper.on('click', '.delete-button', function (e) {
            _this.setStatus('');
            e.preventDefault();

            var form = $(this).parents('form');
            $.ajax({
                type: "DELETE",
                url: _this.deleteEndpoint,
                data: form.serialize(),
                dataType: "json",
                success: function () {
                    var li = form.parents('li.transfer');
                    li.remove();
                    if (_this.thereAreNoRecords(_this.wrapper)) {
                        _this.noTransferWrapper.show();
                    }
                }
            });
        });
    };
    
    
    Transfers.prototype.attachGlobalEvents = function () {
        var _this = this.that;

        $(document).bind("ajaxSend", function(){
            _this.setStatus('');
        }).bind("ajaxSuccess", function () {
            _this.setStatus('Saved');
        }).bind("ajaxError", function () {
            _this.setStatus('Not saved');
        });
    };

    // save callback
    Transfers.prototype.saveTransfer = function (form) {
        var _this = this.that;

        if (!_this.isFormValid(form)) {
            return;
        }

        var idElement = form.find('input[name=id]');
        var isNewRecord = parseInt(idElement.val()) === 0;

        if (isNewRecord) {
            $.post(_this.saveEndpoint, form.serialize())
                    .success(function (data) {
                        // if a new record is added: set <input name=id > value for future editing, add new empty row, and add delete button
                        idElement.val(data.transferId);
                        _this.addEmptyRow(form.parents('li.transfer'));
                        _this.addDeleteButton(form, data);
                        // remove noTransfers checkbox 
                        _this.noTransferWrapper.hide().find('input[type=checkbox]').attr('checked', false);
                    });
        } else {
            $.ajax({
                type: "PUT",
                url: _this.saveEndpoint,
                data: form.serialize(),
                dataType: "json"
            });
        }

    };

    Transfers.prototype.addEmptyRow = function (after) {
        var _this = this.that;

        after.after(_this.emptyRow.html());
    };

    Transfers.prototype.addDeleteButton = function (form, data) {
        var _this = this.that;

        form.find('.delete-button-container').html(_this.renderDeleteButtonAfterEndpointCalled(data));
    };


    opg.Transfers = Transfers;


})(jQuery, opg);
