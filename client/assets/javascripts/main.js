/* globals $ */
import MOJFrontend from '@ministryofjustice/frontend'

require('../scss/application.scss')

const GOVUKFrontend = require('govuk-frontend')

const ShowHideContent = require('./modules/show-hide-content.js')

$(document).ready(function () {
  // Initialising the Show Hide Content GOVUK module
  const showHideContent = new ShowHideContent()
  showHideContent.init()
})

GOVUKFrontend.initAll()
MOJFrontend.initAll()
