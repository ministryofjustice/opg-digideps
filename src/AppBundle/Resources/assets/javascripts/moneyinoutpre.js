/* globals jQuery: true, GOVUK: true */
/* jshint browser: true */
if (typeof GOVUK === 'undefined') {
    GOVUK = {};
}

(function ($, GOVUK) {
    "use strict";

    // Pre Processor to clean the data before it it sent to the server.
    // In the case of money in out we look for totals that have a description and if the person
    // removed the total that needed a description, the description is removed.
    GOVUK.moneyInOuPreprocessor = function (form) {
        
        $(form).find('textarea').each(function (index, textarea) {

            textarea = $(textarea);
            
            // If the text area has a value but the money value doesn't.
            if (textarea.val().replace(/^\s+|\s+$/g, '').length > 0) {

                var transactionTotalElement = textarea.parent().parent().find('input.transaction-value').eq(0);
                var cleanString = transactionTotalElement.val().replace(/[^\d\.\-\ ]/g, '');
                var total = parseFloat(cleanString);
                if (isNaN(total) || total === 0.00) {
                    $(textarea).val('');
                }

            }
        });
    
    };

})(jQuery, GOVUK);
