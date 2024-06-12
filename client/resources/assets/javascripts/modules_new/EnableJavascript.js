const EnableJavascript = {
  init: function (document) {
    document.body.className += ' js-enabled' + ('noModule' in window.HTMLScriptElement.prototype ? ' govuk-frontend-supported' : '')
  }
}

export default EnableJavascript
