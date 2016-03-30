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
        transaction = target.parents('.transaction'),
        lastInputGroup = transaction.find('.form-group:last'),
        nextLine = $(lastInputGroup[0].outerHTML),
        newInput;
      
      // add new line
      lastInputGroup.after(nextLine);

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
      if ( transaction.find('.form-group-value').length > 1) {
          transaction.find('.remove-button').show().off('click').on('click', remove);
      } else {
          transaction.find('.remove-button').hide();
      }
      
      // fix element names to allow submit
      transaction.find('input.transaction-value').each(function(i) {
        var el = $(this);
        var name = el.attr('name');
        el.attr('name', name.slice(0, -3) + '[' + i + ']');
      });
      
  }

  root.GOVUK.accountTransactionExpander = function() {
    $('.transaction').each(function () {
        var inputRows = $(this).find('.form-group-value');
        inputRows.each(function() {
            var currentInput = $(this).find('.transaction-value').eq(0);
            var removeButton = $('<span class="remove-button">Remove</span>');
            removeButton.on('click', remove);
            removeButton.insertAfter(currentInput);
            // show if there is more than one inputbox
            if (inputRows.length > 1) {
                removeButton.show();
            }
        });
    });
    $('.add-transaction a').on('click',addField);
  };

}).call(this);
