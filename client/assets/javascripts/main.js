/* globals $ */
import { ButtonToggler } from './modules/buttonToggler.js'
import { GoogleAnalyticsEvents } from './modules/googleAnalyticsEvents'
import { MOJButtonMenu } from './modules/buttonMenu'
import MOJFrontend from '@ministryofjustice/frontend'

require('../scss/application.scss')
require('./modules/bind.js')

const detailsExpander = require('./modules/detailsExpander.js')
const DetachedDetails = require('./modules/detached-details.js')
const formatCurrency = require('./modules/formatcurrency.js')
const Ga = require('./modules/ga.js')
const GOVUKFrontend = require('govuk-frontend')
const limitChars = require('./modules/characterLimiter.js')
const moneyTransfer = require('./modules/moneyTransfer.js')
const returnHTML = require('./modules/returnHTML.js')
const SessionTimeoutDialog = require('./modules/SessionTimeoutDialog.js')
const ShowHideContent = require('./modules/show-hide-content.js')
const Stickyfill = require('stickyfilljs')
const tableMultiSelect = require('./modules/table-multiselect.js')
const textAreaAutoSize = require('./modules/textarea-autosize.js')
const uploadFile = require('./modules/uploadFile.js')
const uploadProgressPA = require('./modules/uploadProgressPA.js')
const uploadProgress = require('./modules/uploadProgress.js')

/**
 * Taken from govuk-frontend. Supports back to IE8
 * See: https://github.com/alphagov/govuk-frontend/blob/063cd8e2470b62b824c6e50ca66342ac7a95d2d8/src/govuk/common.js#L6
 */
export function nodeListForEach (nodes, callback) {
  if (window.NodeList.prototype.forEach) {
    return nodes.forEach(callback)
  }
  for (let i = 0; i < nodes.length; i++) {
    callback.call(window, nodes[i], i, nodes)
  }
}

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
  const $detachedDetails = document.querySelectorAll(
    '[data-module="opg-detached-details"]'
  )
  nodeListForEach($detachedDetails, function ($el) {
    new DetachedDetails($el).init()
  })

  // Initialising the Show Hide Content GOVUK module
  const showHideContent = new ShowHideContent()
  showHideContent.init()

  const $togglers = document.querySelectorAll(
    '[data-module="opg-button-toggler"]'
  )

  if ($togglers !== null) {
    nodeListForEach($togglers, function ($el) {
      new ButtonToggler().init($el)
    })
  }

  const $menuButtons = $('.moj-button-menu')

  if ($menuButtons !== null) {
    new MOJButtonMenu().init($menuButtons)
  }

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

  const trackableLinks = document.querySelectorAll('.js-trackDownloadLink')

  if (trackableLinks !== null) {
    const ga = new Ga({ timeout: 250 })
    ga.trackDownloadableLink($('.js-trackDownloadLink'))
  }

  const transactionCsvElements = document.querySelectorAll('#transactionsCsv')

  if (transactionCsvElements !== null) {
    const ga = new Ga({ timeout: 250 })
    ga.trackDownloadableLink($('#transactionsCsv'))
  }
})

GOVUKFrontend.initAll()
MOJFrontend.initAll()

// Polyfill elements with position:sticky
const elements = document.querySelectorAll('.opg-sticky-menu')
Stickyfill.add(elements)
