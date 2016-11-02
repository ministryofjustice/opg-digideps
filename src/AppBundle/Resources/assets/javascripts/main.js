// ====================================================================================
// INITITALISE ALL GOVUK MODULES

// Initiating the SelectionButtons GOVUK module
$(document).ready(function() {
	var $blockLabels = $(".block-label input[type='radio'], .block-label input[type='checkbox']");
	new GOVUK.SelectionButtons($blockLabels);
});