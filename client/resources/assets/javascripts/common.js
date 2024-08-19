import SessionTimeout from './globals/SessionTimeout'
import CookieBanner from './globals/CookieBanner'
import ConditionalFieldRevealer from './modules_new/ConditionalFieldRevealer'
import TextAreaAutoSize from './modules_new/TextAreaAutoSize'
import PreventDoubleClickButton from './modules_new/PreventDoubleClickButton'
import PreventDoubleClickLink from './modules_new/PreventDoubleClickLink'
import DetailsExpander from './modules_new/DetailsExpander'
import uploadFile from './modules_new/UploadFile'
import FormatCurrency from './modules_new/FormatCurrency'
import MoneyTransfer from './modules_new/MoneyTransfer'
import TableMultiSelect from './modules_new/TableMultiSelect'
import ReturnHTML from './modules_new/ReturnHTML'
import ShowHideContent from './modules_new/ShowHideContent'
import { initAll as MOJFrontendAll } from '@ministryofjustice/frontend'
import { Accordion, Button, CharacterCount, Checkboxes, ErrorSummary, ExitThisPage, Header, NotificationBanner, PasswordInput, Radios, SkipLink, createAll } from 'govuk-frontend'
import GoogleAnalyticsLinkTracking from './modules_new/GoogleAnalyticsLinkTracking'
import { GoogleAnalyticsEvents } from './modules_new/googleAnalyticsEvents'
import GoogleAnalyticsObject from './modules_new/GoogleAnalyticsObject'
import GoogleAnalyticsGtag from './modules_new/GoogleAnalyticsGtag'
import EnableJavascript from './modules_new/EnableJavascript'

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

  MoneyTransfer.init(document)

  TableMultiSelect.init(document)

  ReturnHTML.init(document)

  ShowHideContent.init()

  EnableJavascript.init(document)

  GoogleAnalyticsEvents.init()

  GoogleAnalyticsLinkTracking.init(document, 250)

  GoogleAnalyticsObject.init(document)

  GoogleAnalyticsGtag.init(document)

  // Error summaries
  const errorSummaries = document.querySelector('#error-summary')
  if (errorSummaries !== null) {
    errorSummaries.focus()
  }

  createAll(Accordion)
  createAll(Button)
  createAll(CharacterCount)
  createAll(Checkboxes)
  createAll(ErrorSummary)
  createAll(ExitThisPage)
  createAll(Header)
  createAll(NotificationBanner)
  createAll(PasswordInput)
  createAll(Radios)
  createAll(SkipLink)

  MOJFrontendAll()
})
