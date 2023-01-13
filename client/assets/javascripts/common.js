import SessionTimeout from './globals/SessionTimeout'
import CookieBanner from './globals/CookieBanner'
import MOJButtonMenu from './modules_new/ButtonMenu'
import ButtonToggler from './modules_new/ButtonToggler'
import CharacterLimiter from './globals/CharacterLimiter'
import ConditionalFieldRevealer from './modules_new/ConditionalFieldRevealer'
import Redirector from './globals/Redirector'
import TextAreaAutoSize from './modules_new/TextAreaAutoSize'

window.addEventListener('DOMContentLoaded', (event) => {
  // Session Timeout
  SessionTimeout()

  // Cookie Banner
  CookieBanner()

  // Menu Buttons
  const menuButtons = document.querySelector('.moj-button-menu')
  if (menuButtons.length > 0) {
    MOJButtonMenu.init(menuButtons)
  }

  // Toggleable Buttons
  const btnTogglers = document.querySelectorAll('[data-module="opg-button-toggler"]')
  if (btnTogglers.length > 0) {
    ButtonToggler.init(btnTogglers)
  }

  // Character Limiter
  CharacterLimiter.init()

  // Conditional Field Revealer
  ConditionalFieldRevealer.init()

  // Redirector
  Redirector()

  // Text Area Auto Size
  const formArea = document.querySelectorAll('form')
  if (formArea.length > 0) {
    TextAreaAutoSize.init(formArea)
  }
})
