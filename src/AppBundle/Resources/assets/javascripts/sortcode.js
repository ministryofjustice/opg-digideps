var opg = opg || {};

(function ($, opg) {
    
    var sortCodeParts;
    var container;
    var noneNumericError = 'Sort code must be numeric';
    var errors;

    function skipToNext(event) {
        
        var target = $(event.target);
        
        if(target.val().length == target.attr('maxlength') && target[0] !== sortCodeParts[sortCodeParts.length -1] ) {
            var nextField = $('input', target.parent().nextAll(".form-group")[0])
            nextField.focus();
        }    
    }

    function showErrorDescription() {
        if ($('.form-sort-code .field-with-errors').length > 0){
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
    
    opg.SortCodeValidate = function(target) {
        container = $(target);
        errors = container.find('.errors');
        sortCodeParts = container.find(".sort-code-part");
        
        sortCodeParts.on('propertychange input', skipToNext);
        sortCodeParts.on('blur', validateField);
    };

})(jQuery, opg);