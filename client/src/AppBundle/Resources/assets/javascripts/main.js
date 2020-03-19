/* globals $ */
require('../scss/application.scss')
require('./modules/bind.js')

var GOVUKFrontend = require('govuk-frontend')
var Stickyfill = require('stickyfilljs')
var limitChars = require('./modules/characterLimiter.js')
var cookieBanner = require('./modules/cookieBanner.js')
var detailsExpander = require('./modules/detailsExpander.js')
var DetachedDetails = require('./modules/detached-details.js')
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

  // Cookie banner
  cookieBanner()

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
  uploadProgressPA('[data-module="csv-upload-progress"]')

  // Table Multi Select
  tableMultiSelect()

  // Detached details/summary
  document.querySelectorAll('[data-module="opg-detached-details"]').forEach(function ($el) {
    new DetachedDetails($el).init()
  })

  // Initialising the Show Hide Content GOVUK module
  var showHideContent = new ShowHideContent()
  showHideContent.init()
})

GOVUKFrontend.initAll()

// Polyfill elements with position:sticky
var elements = document.querySelectorAll('.opg-sticky-menu')
Stickyfill.add(elements)
