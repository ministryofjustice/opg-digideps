// ====================================================================================
// INITITALISE ALL MODULES

$(document).ready(function() {

	// SelectionButtons GOVUK module
	var $blockLabels = $(".block-label input[type='radio'], .block-label input[type='checkbox']");
	new GOVUK.SelectionButtons($blockLabels);

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