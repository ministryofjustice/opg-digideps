var PageReloader = function() {
    // "use strict";

    var that = this;

    this.addReloaderClickListener = function (elementId) {
        var btn = document.getElementById(elementId);

        btn.addEventListener('click', function() {
            setTimeout(window.location.reload.bind(window.location), 5000)
        });
    }

};
