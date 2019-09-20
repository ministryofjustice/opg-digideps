// ====================================================================================
// INITITALISE ALL MODULES
require('../scss/application.scss');
require('../scss/formatted-report.scss');

require('./modules/bind.js');
require('./modules/characterLimiter.js');
require('./modules/detailsExpander.js');
require('./modules/fix-sidebar.js');
require('./modules/formatcurrency.js');
require('./modules/ga.js');
require('./modules/moneyTransfer.js');
require('./modules/returnHTML.js');
require('./modules/sessionTimeoutDialog.js');
require('./modules/show-hide-content.js');
require('./modules/submit.js');
require('./modules/table-multiselect.js');
require('./modules/textarea-autosize.js');
require('./modules/upload.js');

$(document).ready(function() {

    // JS induced disabling of elements
    $('.js-disabled').attr('disabled', 'disabled');

	// Format currency module
	$('.js-format-currency').on('blur', function (event) {
        GOVUK.formatCurrency(event.target);
    });

    // Character limiter module
    new GOVUK.limitChars('form');

    // Text area autoSize module
    new GOVUK.textAreaAutoSize('form');

    // Sidebar fixing to top module
    new GOVUK.fixSidebar();

    // Details expander
    new GOVUK.detailsExpander('.js-details-expander');
    new GOVUK.detailsExpander('.js-details-expander-travel-costs');
    new GOVUK.detailsExpander('.js-details-expander-specialist-service');

    // Upload Files
    new GOVUK.uploadFile('.js-uploading');

    // Return HTML with ajax
    new GOVUK.returnHTML('.js-return-html');

    // Money transfer
    new GOVUK.moneyTransfer('.js-transfer-from');

    // Check upload progress
    new GOVUK.uploadProgress('.js-upload-progress');
    new GOVUK.uploadProgressPA('.js-upload-progress-pa');

    // Table Multi Select
    new GOVUK.tableMultiSelect();

    // Initialising the Show Hide Content GOVUK module
    var showHideContent = new GOVUK.ShowHideContent();
    showHideContent.init();

});

GOVUKFrontend.initAll();
