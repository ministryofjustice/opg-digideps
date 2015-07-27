var opg = opg || {};

(function ($, opg) {

    opg.accordian = function(options) {

        var container,
            open = true,
            heading,
            content;

        function handleClick(event) {
            open = !open;
            if (open) {
                container.addClass('open').removeClass('closed');
                content.show();
            } else {
                container.addClass('closed').removeClass('open');
                content.hide();
            }            
        }
        
        function init(options) {
            container = $(options.target);
            
            if (options.hasOwnProperty('open')) {
                open = options.open;
            }
            
            if (open) {
                container.addClass('open');
            } else {
                container.addClass('closed');
            }
            
            container.addClass('accordian');
            
            heading = container.find('.summary');
            content = container.find('.content');
            heading.on('click', handleClick);
        }
        
        init(options);

    };

})(jQuery, opg);