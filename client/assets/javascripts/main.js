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
const Stickyfill = require('stickyfilljs')
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

  const $submitButtons = document.querySelectorAll(
    '[data-module="opg-toggleable-submit"]'
  )

  if ($submitButtons !== null) {
    $submitButtons.forEach(function ($el) {
      $el.addEventListener('click', function ($e) {
        $e.target.classList.add(
          'opg-submit-link--disabled',
          'govuk-button--disabled'
        )

        setTimeout(function () {
          $e.target.classList.remove(
            'opg-submit-link--disabled',
            'govuk-button--disabled'
          )
        }, 3000)
      })
    })
  }

  // Error summaries
  const $errorSummaries = document.querySelectorAll('#error-summary')
  if ($errorSummaries !== null) {
    $errorSummaries.forEach((ele) => {
      ele.focus()
    })
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

// Polyfill elements with position:sticky
const elements = document.querySelectorAll('.opg-sticky-menu')
Stickyfill.add(elements)
