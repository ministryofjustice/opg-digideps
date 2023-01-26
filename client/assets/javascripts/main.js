/* globals $ */
import { GoogleAnalyticsEvents } from './modules/googleAnalyticsEvents'
import MOJFrontend from '@ministryofjustice/frontend'

require('../scss/application.scss')

const GOVUKFrontend = require('govuk-frontend')

const detailsExpander = require('./modules/detailsExpander.js')
const formatCurrency = require('./modules/formatcurrency.js')
const Ga = require('./modules/ga.js')
const moneyTransfer = require('./modules/moneyTransfer.js')
const returnHTML = require('./modules/returnHTML.js')
const SessionTimeoutDialog = require('./modules_new/SessionTimeoutDialog.js')
const ShowHideContent = require('./modules/show-hide-content.js')
const tableMultiSelect = require('./modules/table-multiselect.js')
const uploadFile = require('./modules/uploadFile.js')

window.opg = {
  Ga,
  SessionTimeoutDialog
}

$(document).ready(function () {
  // Format currency module
  $('.js-format-currency').on('blur', function (event) {
    formatCurrency(event.target)
  })

  // Details expander
  detailsExpander('.js-details-expander')

  // Upload Files
  uploadFile('.js-uploading')

  // Return HTML with ajax
  returnHTML('.js-return-html')

  // Money transfer
  moneyTransfer('.js-transfer-from')

  // Table Multi Select
  tableMultiSelect()

  // Initialising the Show Hide Content GOVUK module
  const showHideContent = new ShowHideContent()
  showHideContent.init()

  // Error summaries
  const $errorSummaries = document.querySelector('#error-summary')
  if ($errorSummaries !== null) {
    $errorSummaries.focus()
  }

  GoogleAnalyticsEvents.init()
  GoogleAnalyticsEvents.initFormValidationErrors()

  const trackableLinks = document.querySelectorAll('.js-trackDownloadLink')

  if (trackableLinks !== null) {
    const ga = new Ga({ timeout: 250 })
    ga.trackDownloadableLink($('.js-trackDownloadLink'))
  }
})

GOVUKFrontend.initAll()
MOJFrontend.initAll()
