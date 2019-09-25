/* globals $ */
require('../scss/application.scss')
require('./modules/bind.js')

var GOVUKFrontend = require('govuk-frontend')
var limitChars = require('./modules/characterLimiter.js')
var detailsExpander = require('./modules/detailsExpander.js')
var fixSidebar = require('./modules/fix-sidebar.js')
var formatCurrency = require('./modules/formatcurrency.js')
var Ga = require('./modules/ga.js')
var moneyTransfer = require('./modules/moneyTransfer.js')
var returnHTML = require('./modules/returnHTML.js')
var SessionTimeoutDialog = require('./modules/SessionTimeoutDialog.js')
var ShowHideContent = require('./modules/show-hide-content.js')
var tableMultiSelect = require('./modules/table-multiselect.js')
var textAreaAutoSize = require('./modules/textarea-autosize.js')
var uploadFile = require('./modules/uploadFile.js')
var uploadProgressPA = require('./modules/uploadProgressPA.js')
var uploadProgress = require('./modules/uploadProgress.js')

window.opg = {
  Ga: Ga,
  SessionTimeoutDialog: SessionTimeoutDialog
}

$(document).ready(function () {
  // JS induced disabling of elements
  $('.js-disabled').attr('disabled', 'disabled')

  // Format currency module
  $('.js-format-currency').on('blur', function (event) {
    formatCurrency(event.target)
  })

  // Character limiter module
  limitChars('form')

  // Text area autoSize module
  textAreaAutoSize('form')

  // Sidebar fixing to top module
  fixSidebar()

  // Details expander
  detailsExpander('.js-details-expander')
  detailsExpander('.js-details-expander-travel-costs')
  detailsExpander('.js-details-expander-specialist-service')

  // Upload Files
  uploadFile('.js-uploading')

  // Return HTML with ajax
  returnHTML('.js-return-html')

  // Money transfer
  moneyTransfer('.js-transfer-from')

  // Check upload progress
  uploadProgress('.js-upload-progress')
  uploadProgressPA('.js-upload-progress-pa')

  // Table Multi Select
  tableMultiSelect()

  // Initialising the Show Hide Content GOVUK module
  var showHideContent = new ShowHideContent()
  showHideContent.init()
})

GOVUKFrontend.initAll()
