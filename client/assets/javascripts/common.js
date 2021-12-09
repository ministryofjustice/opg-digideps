import SessionTimeout from './globals/sessionTimeout'
import CookieBanner from './modules/cookieBanner'

window.addEventListener('DOMContentLoaded', (event) => {
  // Session Timeout Handler
  SessionTimeout()

  // Cookie Banner Handler
  CookieBanner()
})
