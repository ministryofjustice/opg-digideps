// ====================================================================================
// INITITALISE ALL MODULES

$(document).ready(function() {

	// Format currency module
	$('.js-format-currency').on('blur', function (event) {
        GOVUK.formatCurrency(event.target);
    });

    // Character limiter module
    new GOVUK.limitChars('form');

    // Details expander
    new GOVUK.detailsExpander('.js-details-expander');

    // Upload Files
    new GOVUK.uploadFile('.js-uploading');

    // Initialising the Show Hide Content GOVUK module
    var showHideContent = new GOVUK.ShowHideContent();
    showHideContent.init();
});