// ====================================================================================
// INITITALISE ALL MODULES

$(document).ready(function() {

	// Initialising the SelectionButtons GOVUK module
	var $blockLabels = $(".block-label input[type='radio'], .block-label input[type='checkbox']");
	new GOVUK.SelectionButtons($blockLabels);

	// Initialising the format currency module
	$('.js-format-currency').on('blur', function (event) {
        GOVUK.formatCurrency(event.target);
    });
});