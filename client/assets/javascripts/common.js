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

window.addEventListener('DOMContentLoaded', () => {
  // Session Timeout
  SessionTimeout()

  // Cookie Banner
  CookieBanner()

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
})
