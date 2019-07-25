/*jshint browser: true */
(function () {
    "use strict";

    var root = this,
        $ = root.jQuery;

    if (typeof GOVUK === 'undefined') {
        root.GOVUK = {};
    }

    var tableMultiSelect = function () {

        // Select all checkbox change
        $('.js-checkbox-all').change(function() {
            $('.js-disabled').removeAttr('disabled');

            // Change all '.js-checkbox' checked status
            $('.js-checkbox').prop('checked', $(this).prop('checked'));

            // Toggle checked class on other checkboxes
            if($(this).prop('checked')) {
                $('.js-checkbox').parents('tr').addClass('checked');
            } else {
                $('.js-checkbox').parents('tr').removeClass('checked');
                $('.js-disabled').attr('disabled', 'disabled');
            }

            var caseString = $('.js-checkbox:checked').length == 1 ? ' case' : ' cases';
            $('#numberOfCases').text($('.js-checkbox:checked').length + caseString);
        });

        //'.js-checkbox' change
        $('.js-checkbox').change(function(){
            $('.js-disabled').removeAttr('disabled');

            $(this).parents('tr').toggleClass('checked');

            //uncheck 'select all', if one of the listed checkbox item is unchecked
            if(false == $(this).prop('checked')){
            //change 'select all' checked status to false
                $('.js-checkbox-all').prop('checked', false);
            }

            //check 'select all' if all checkbox items are checked
            if ($('.js-checkbox:checked').length == $('.js-checkbox').length ){
                $('.js-checkbox-all').prop('checked', true);
            } else if ($('.js-checkbox:checked').length == 0 ) {
                $('.js-disabled').attr('disabled', 'disabled');
            }

            var caseString = $('.js-checkbox:checked').length == 1 ? ' case' : ' cases';
            $('#numberOfCases').text($('.js-checkbox:checked').length + caseString);
        });

    };

    root.GOVUK.tableMultiSelect = tableMultiSelect;

}).call(this);




