/* globals $ */
import MOJFrontend from '@ministryofjustice/frontend'

require('../scss/application.scss')

const GOVUKFrontend = require('govuk-frontend')

const Ga = require('./modules/ga.js')
const returnHTML = require('./modules/returnHTML.js')
const ShowHideContent = require('./modules/show-hide-content.js')
const tableMultiSelect = require('./modules/table-multiselect.js')

window.opg = {
  Ga
}

$(document).ready(function () {
  // Return HTML with ajax
  returnHTML('.js-return-html')

  // Table Multi Select
  tableMultiSelect()

  // Initialising the Show Hide Content GOVUK module
  const showHideContent = new ShowHideContent()
  showHideContent.init()

  const trackableLinks = document.querySelectorAll('.js-trackDownloadLink')

  if (trackableLinks !== null) {
    const ga = new Ga({ timeout: 250 })
    ga.trackDownloadableLink($('.js-trackDownloadLink'))
  }
})

GOVUKFrontend.initAll()
MOJFrontend.initAll()
