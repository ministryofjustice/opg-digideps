/*jshint browser: true */
(function () {
    "use strict";

    var root = this,
        $ = root.jQuery;
    
    if (typeof GOVUK === 'undefined') { root.GOVUK = {}; }
    
    
    root.GOVUK.formatCurrency = function(element) {
        element = $(element);
        var number = element.val();
        
        if (number.replace(/^\s+|\s+$/g, '') === '' || isNaN(number)) {
            return;
        }

        var decimalplaces = 2;
        var decimalcharacter = ".";
        var thousandseparater = ",";
        number = parseFloat(number);
        
        var negative = number < 0;
        
        var formatted = String(number.toFixed(decimalplaces));
        if( decimalcharacter.length && decimalcharacter != "." ) { formatted = formatted.replace(/\./,decimalcharacter); }
        var integer = "";
        var fraction = "";
        var strnumber = String(formatted);
        var dotpos = decimalcharacter.length ? strnumber.indexOf(decimalcharacter) : -1;
        if( dotpos > -1 ) {
            if( dotpos ) { integer = strnumber.substr(0,dotpos); }
            fraction = strnumber.substr(dotpos+1);
        }
        else { integer = strnumber; }
        if( integer ) { integer = String(Math.abs(integer)); }
        while( fraction.length < decimalplaces ) { fraction += "0"; }
        var temparray = [];

        while( integer.length > 3 ) {
            // substr with negative indexes does not work on IE.
            // substr is overridden in iefix.js (in case it's wrongly implemented)
            // tested in FF and IE
            temparray.unshift(integer.substr(-3));
            integer = integer.substr(0,integer.length-3);
        }

        temparray.unshift(integer);
        integer = temparray.join(thousandseparater);
    
        var formattedStr = integer + decimalcharacter + fraction;
        if (negative) {
            formattedStr = '-' + formattedStr;
        }

        element.val(formattedStr);

    };
    
}).call(this);
