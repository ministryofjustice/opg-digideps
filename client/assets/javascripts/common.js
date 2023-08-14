import SessionTimeout from './globals/SessionTimeout'
import CookieBanner from './globals/CookieBanner'
import ConditionalFieldRevealer from './modules_new/ConditionalFieldRevealer'
import TextAreaAutoSize from './modules_new/TextAreaAutoSize'
import PreventDoubleClickButton from './modules_new/PreventDoubleClickButton'
import PreventDoubleClickLink from './modules_new/PreventDoubleClickLink'
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
import EnableJavascript from './modules_new/EnableJavascript'
import GoogleAnalyticsObject from './modules_new/GoogleAnalyticsObject'
import GoogleAnalyticsGtag from './modules_new/GoogleAnalyticsGtag'

window.addEventListener('DOMContentLoaded', () => {
  // Session Timeout
  SessionTimeout()

  // Cookie Banner
  CookieBanner()

  // Format currency module
  FormatCurrency.init(document)

  // Double Click Buttons
  PreventDoubleClickButton.init(document)

  // Double Click Links
  PreventDoubleClickLink.init(document)

  // Conditional Field Revealer
  ConditionalFieldRevealer.init()

  // Text Area Auto Size

  TextAreaAutoSize.init(document)

  DetailsExpander.init(document)

  uploadFile.init(document)

  GoogleAnalyticsEvents.init()

  MoneyTransfer.init(document)

  TableMultiSelect.init(document)

  ReturnHTML.init(document)

  GoogleAnalyticsLinkTracking.init(document, 250)

  ShowHideContent.init()

  EnableJavascript.init(document)

  GoogleAnalyticsObject.init(document)

  GoogleAnalyticsGtag.init(document)

  // Error summaries
  const errorSummaries = document.querySelector('#error-summary')
  if (errorSummaries !== null) {
    errorSummaries.focus()
  }

  GOVUKFrontend.initAll()
  MOJFrontend.initAll()
})
