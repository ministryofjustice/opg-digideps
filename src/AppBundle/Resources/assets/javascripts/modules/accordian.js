/* globals jQuery */
var opg = opg || {};

// Call with element, e.g. opg.accordian($('.test'));
// of  $('.test').each(function(index,element) { opg.accordian({target:element})}; 
// of opg.accordian('#single');

(function ($, opg) {

    opg.accordian = function(options) {

        var container,
            openState = true,
            heading,
            content;

        function close() {
            container.addClass('closed').removeClass('open');
            content.hide();
        }
        function open() {
            container.addClass('open').removeClass('closed');
            content.show();
        }
        
        function handleClick() {
            openState = !openState;
            if (openState) {
                open();
            } else {
                close();
            }            
        }

        function init(options) {
            container = $(options.target);
            
            if (options.hasOwnProperty('open')) {
                openState = options.open;
            }
                        
            container.addClass('accordian');
            
            heading = container.find('.summary');
            content = container.find('.content');
            heading.on('click', handleClick);

            if (openState) {
                open();
            } else {
                close();
            }
        }
        
        init(options);

    };

})(jQuery, opg);