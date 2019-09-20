// Fix sidebar to top when scrolling
// Use the class name of .js-side-bar fix on the sidebar under the column div.
// Note that associated styles live under _panel.scss.

module.exports = function () {
    var $sidebar = $('.js-sidebar');
    if (!$sidebar.length) return;

    var sidebarOffset = $sidebar.offset().top;

    $(window).scroll(function(){
        scrollTop = $(window).scrollTop();

        if (scrollTop >= sidebarOffset) {
        $('.js-sidebar').addClass('opg-related-items--fixed');
        }

        if (scrollTop < sidebarOffset) {
        $('.js-sidebar').removeClass('opg-related-items--fixed');
        }

    });
};
