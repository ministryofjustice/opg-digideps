/*jshint browser: true */
(function () {
  "use strict";

  var root = this,
      $ = root.jQuery;

  if (typeof GOVUK === 'undefined') { root.GOVUK = {}; }

  function fakeSave () {
      var statusElement = $('#page-section-title-container').find('.info');
      statusElement.html('<span id="save-status" data-status="saving">Saving...</span>');
      window.setTimeout(function () {
          statusElement.html('<span id="save-status" data-status="saved">Saved</span>');
      }, 1000);
      window.setTimeout(function () {
          statusElement.html('');
      }, 2000);
  }
  function remove(event) {

      var target = $(event.target);
      var field = target.prev();

      field.val("");
      field.trigger('recalc');

      var transaction = target.parent().parent();
      target.parent().remove();

      var groups = transaction.find('.form-group-value');
      if (groups.length > 1) {
          transaction.find('.remove-button').show();
      } else {
          transaction.find('.remove-button').hide();
      }

      groups.each(function (index, element) {
          if (index === 0) {
              $(element).find('label').show();
          } else {
              $(element).find('label').hide();
          }
      });

  }
  function addField(event) {
      event.preventDefault();

      var target = $(event.target);
      var existing = target.parent().prev();
      var nextLine = $(existing[0].outerHTML);
      existing.after(nextLine);

      nextLine.find('input.transaction-value').val("")
          .on('keyup', function () {
              $(window).trigger({type:'TRANSACTION_TOTAL_CHANGE', input: event.target});
          })
          .on('blur', function (event) {
              root.GOVUK.formatCurrency(event.target);
              fakeSave();
          })
          .focus();

      nextLine.find('label').hide();

      var transaction = target.parent().parent();
      var groups = transaction.find('.form-group-value');

      if (groups.length > 1) {
          transaction.find('.remove-button').show().off('click').on('click', remove);
      } else {
          transaction.find('.remove-button').hide();
      }

  }

  root.GOVUK.accountTransactionExpander = function() {
    $('.transaction').each(function (index, element) {

        var transactionElement = $(element);

        var currentField = transactionElement.find('.form-group-value');
        var currentInput = currentField.find('.transaction-value').eq(0);

        var removeButton = $('<span class="remove-button">Remove</span>');
        removeButton.on('click', remove);
        removeButton.insertAfter(currentInput);
    });
    $('.add-transaction a').on('click',addField);
  };

}).call(this);
