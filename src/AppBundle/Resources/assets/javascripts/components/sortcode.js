$( document ).ready(function() {
    var sortCodeParts = $(".sort-code-part");
    var container = $(sortCodeParts[0]).parent().parents('.form-group');

    sortCodeParts.on('input', function(event) {
        
        var target = $(event.target);
        
        if(target.val().length == target.attr('maxlength') && target[0] !== sortCodeParts[sortCodeParts.length -1] ) {
            $('input', target.parent().nextAll(".form-group")[0]).focus();
        }    
    
    });
    
    function validate() {
        
        var valid = true;
        
        sortCodeParts.each(function(index, element) {
            var $element = $(element);
            var str = $element.val();
            var parent = $element.parent();

            // Make sure the field only contains 2 digits
            if (/^\d{1,2}$/.test(str) && str.length === 2) {
                parent.removeClass('field-with-errors');
            } else {
                parent.addClass('field-with-errors');
                valid = false;
            }
        });
        
        if (valid) {
            container.removeClass('field-with-errors');
        } else {
            container.addClass('field-with-errors');
        }
        
    }
    
    sortCodeParts.on('blur', validate);

});