// ====================================================================================
// INITITALISE ALL MODULES

$(document).ready(function() {

    // JS induced disabling of elements
    $('.js-disabled').attr('disabled', 'disabled');

	// Format currency module
	$('.js-format-currency').on('blur', function (event) {
        GOVUK.formatCurrency(event.target);
    });

    // Character limiter module
    new GOVUK.limitChars('form');

    // Details expander
    new GOVUK.detailsExpander('.js-details-expander');
    new GOVUK.detailsExpander('.js-details-expander-travel-costs');
    new GOVUK.detailsExpander('.js-details-expander-specialist-service');

    // Upload Files
    new GOVUK.uploadFile('.js-uploading');

    // Return HTML with ajax
    new GOVUK.returnHTML('.js-return-html');

    // Check upload progress
    new GOVUK.uploadProgress('.js-upload-progress');
    new GOVUK.uploadProgressPA('.js-upload-progress-pa');

    // Table Multi Select
    new GOVUK.tableMultiSelect();

    // Initialising the Show Hide Content GOVUK module
    var showHideContent = new GOVUK.ShowHideContent();
    showHideContent.init();
});