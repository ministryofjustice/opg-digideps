import { Redirector } from '../modules/redirector'

window.addEventListener('DOMContentLoaded', (event) => {
  const url = document.querySelector('[data-redirect-url]').dataset.redirectUrl
  Redirector(url, document)
})
