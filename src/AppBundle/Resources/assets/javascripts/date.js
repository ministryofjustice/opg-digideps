var opg = opg || {};

(function ($, opg) {
    
    var dateParts;
    var container;
    var noneNumericError = 'Date code must be numeric';
    var errors;

    function skipToNext(event) {
        
        var target = $(event.target);
        
        if(target.val().length == target.attr('maxlength') && target[0] !== dataParts[dateParts.length -1] ) {
            var nextField = $('input', target.parent().nextAll(".form-group")[0]);
            nextField.focus();
        }    
    }

    function showErrorDescription() {
        if ($('.form-date .field-with-errors').length > 0){
            errors.empty().append('<li class="error">' + noneNumericError + '</li>');    
            container.addClass('field-with-errors');  
        } else {
            container.removeClass('field-with-errors');
            errors.empty();
        }
    }

    function validateField(event) {
        
        var field = $(event.target);
        var str = field.val();
        var parent = field.parent();

        if (/^\d{1,2}$/.test(str) && str.length === 2) {
            parent.removeClass('field-with-errors');
        } else {
            parent.addClass('field-with-errors');
        }

        showErrorDescription();
    }
    
    opg.DateValidate = function(target) {
        container = $(target);
        errors = container.find('.errors');
        dateParts = container.find(".date-part");
        
        dateParts.on('propertychange input', skipToNext);
        dateParts.on('blur', validateField);
    };

})(jQuery, opg);