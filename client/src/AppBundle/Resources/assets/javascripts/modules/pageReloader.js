"use strict";

var PageReloader = function(selector) {
    $(selector).on('click', function() {
        setTimeout(function() {
            window.location.reload()
        },5000)
    });
};
