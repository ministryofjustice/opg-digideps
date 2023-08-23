/* globals ga */
module.exports = {
  init: function (document) {
    (function (i, s, o, g, r, a, m) {
      i.GoogleAnalyticsObject = r
      i[r] = i[r] || function () {
        (i[r].q = i[r].q || []).push(arguments)
      }
      i[r].l = 1 * new Date()
      a = s.createElement(o)
      m = s.getElementsByTagName(o)[0]
      a.async = 1
      a.src = g
      m.parentNode.insertBefore(a, m)
    })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga')

    // Check we have permission
    const cookiePolicyJSON = document.cookie.replace(/(?:(?:^|.*;\s*)cookie_policy\s*=\s*([^;]*).*$)|^.*$/, '$1')
    const cookiePolicy = cookiePolicyJSON ? JSON.parse(decodeURIComponent(cookiePolicyJSON)) : {}

    const gaCustomElement = document.getElementById('gaCustomElements')
    const gaDefault = gaCustomElement ? gaCustomElement.getAttribute('data-ga-default') : undefined
    const gaGds = gaCustomElement ? gaCustomElement.getAttribute('data-ga-gds') : undefined
    const gaTrackingId = gaCustomElement ? gaCustomElement.getAttribute('data-tracking-id') : undefined
    const gaCustomUrl = gaCustomElement ? gaCustomElement.getAttribute('data-ga-custom-url') : undefined

    if (cookiePolicy.usage) {
      ga('create', gaDefault, 'auto')
      ga('create', gaGds, 'auto', 'govuk_shared', { allowLinker: true })
      ga('govuk_shared.require', 'linker')
      ga('govuk_shared.linker.set', 'anonymizeIp', true)
      ga('govuk_shared.linker:autoLink', ['www.gov.uk'])

      if (typeof gaTrackingId !== 'undefined') {
        ga('set', '&uid', gaTrackingId)
      }

      if (typeof gaCustomUrl !== 'undefined') {
        ga('send', 'pageview', gaCustomUrl)
        ga('govuk_shared.send', 'pageview', gaCustomUrl)
      } else {
        ga('send', 'pageview')
        ga('govuk_shared.send', 'pageview')
        // DISABLE all the query strings to be sent to GA. Not needed for Assets and bank accounts, as already customized
        // if (typeof app.request !== 'undefined' && app.request) {
        //   ga('send', 'pageview', app.request.schemeAndHttpHost() + app.request.baseUrl() + app.request.pathInfo());
        // }
      }
    }
  }
}
