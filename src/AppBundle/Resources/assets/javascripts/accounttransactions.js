/*jshint browser: true */
(function () {
  "use strict";

  var root = this,
      $ = root.jQuery;

  if (typeof GOVUK === 'undefined') { root.GOVUK = {}; }

  function remove(event) {

      var target = $(event.target),
          thisGroup = target.parent(),
          transaction = thisGroup.parent(),
          removeButton = transaction.find('.remove-button');

      thisGroup.remove();

      var groups = transaction.find('.form-group-value');

      if (groups.length > 1) {
          removeButton.show();
      } else {
          removeButton.hide();
      }

      groups.each(function (index, element) {
          if (index === 0) {
              $(element).find('label').show();
          } else {
              $(element).find('label').hide();
          }
      });

      transaction.trigger('removeField');
  }
  function addField(event) {
      event.preventDefault();

      var target = $(event.target),
        transaction = target.parent().parent(),
        existing = target.parent().prev(),
        nextLine = $(existing[0].outerHTML),
        newInput;

      existing.after(nextLine);

      newInput = nextLine.find('input.transaction-value');

      // trigger AddField
      newInput
        .val("")
        .trigger('addField')
        .on('blur', function (event) {
          root.GOVUK.formatCurrency(event.target);
        })
        .focus();

      nextLine.find('label').hide();

      // add "remove button" if there is more than one input
      if ( transaction.find('.form-group-value') > 1) {
          transaction.find('.remove-button').show().off('click').on('click', remove);
      } else {
          transaction.find('.remove-button').hide();
      }
      
      //fix element names to allow submit
      alert('FIXME');

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
