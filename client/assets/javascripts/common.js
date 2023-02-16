import SessionTimeout from './globals/SessionTimeout'
import CookieBanner from './globals/CookieBanner'
import MOJButtonMenu from './modules_new/ButtonMenu'
import ButtonToggler from './modules_new/ButtonToggler'
import ConditionalFieldRevealer from './modules_new/ConditionalFieldRevealer'
import TextAreaAutoSize from './modules_new/TextAreaAutoSize'
import DoubleClickProtection from './modules_new/DoubleClickProtection'
import DetailsExpander from './modules_new/DetailsExpander'
import uploadFile from './modules_new/UploadFile'
import { GoogleAnalyticsEvents } from './modules_new/googleAnalyticsEvents'
import FormatCurrency from './modules_new/FormatCurrency'
import MoneyTransfer from './modules_new/MoneyTransfer'
import TableMultiSelect from './modules_new/TableMultiSelect'
import ReturnHTML from './modules_new/ReturnHTML'
import GoogleAnalyticsLinkTracking from './modules_new/GoogleAnalyticsLinkTracking'
import ShowHideContent from './modules_new/ShowHideContent'
import MOJFrontend from '@ministryofjustice/frontend'
import GOVUKFrontend from 'govuk-frontend'

window.addEventListener('DOMContentLoaded', () => {
  // Session Timeout
  SessionTimeout()

  // Cookie Banner
  CookieBanner()

  // Format currency module
  FormatCurrency.init(document)

  // Menu Buttons
  const menuButtons = document.querySelectorAll('.moj-button-menu')
  if (menuButtons.length > 0) {
    MOJButtonMenu.init(menuButtons)
  }

  // Toggleable Buttons
  const btnTogglers = document.querySelectorAll('[data-module="opg-button-toggler"]')
  if (btnTogglers.length > 0) {
    ButtonToggler.init(btnTogglers)
  }

  // Conditional Field Revealer
  ConditionalFieldRevealer.init()

  // Text Area Auto Size

  TextAreaAutoSize.init(document)

  DoubleClickProtection.init(document)

  DetailsExpander.init(document)

  uploadFile.init(document)

  GoogleAnalyticsEvents.init()
  GoogleAnalyticsEvents.initFormValidationErrors()

  MoneyTransfer.init(document)

  TableMultiSelect.init(document)

  ReturnHTML.init(document)

  GoogleAnalyticsLinkTracking.init(document, 250)

  ShowHideContent.init()

  // Error summaries
  const errorSummaries = document.querySelector('#error-summary')
  if (errorSummaries !== null) {
    errorSummaries.focus()
  }

  GOVUKFrontend.initAll()
  MOJFrontend.initAll()
})
