// Fix sidebar to top when scrolling
// Use the class name of .js-side-bar fix on the sidebar under the column div.
// Note that associated styles live under _panel.scss.

(function () {
    // "use strict";

    var root = this,
        $ = root.jQuery;

    if (typeof GOVUK === 'undefined') {
        root.GOVUK = {};
    }

    var fixSidebar = function () {

        var $sidebar = $('.js-sidebar');
        if (!$sidebar.length) return;

        var sidebarOffset = $sidebar.offset().top;

        $(window).scroll(function(){
          scrollTop = $(window).scrollTop();

          if (scrollTop >= sidebarOffset) {
            $('.js-sidebar').addClass('sidebar-fix-top');
          }

          if (scrollTop < sidebarOffset) {
            $('.js-sidebar').removeClass('sidebar-fix-top');
          }

        });
    };

    root.GOVUK.fixSidebar = fixSidebar;

}).call(this);
