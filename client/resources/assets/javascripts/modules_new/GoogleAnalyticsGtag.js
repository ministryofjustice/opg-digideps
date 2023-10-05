/* globals dataLayer */
module.exports = {
  init: function (document) {
    const gaCustomElement = document.getElementById('gaCustomElements')
    const gaDefault = gaCustomElement ? gaCustomElement.getAttribute('data-ga-default') : undefined
    window.dataLayer = window.dataLayer || []
    function gtag () { dataLayer.push(arguments) }
    gtag('js', new Date())
    gtag('config', gaDefault)

    window.globals = (() => {
      function gtagWrapper (event, eventName, eventParameters) {
        gtag(event, eventName, eventParameters)
      }

      return {
        gtag: gtagWrapper
      }
    })()
  }
}
