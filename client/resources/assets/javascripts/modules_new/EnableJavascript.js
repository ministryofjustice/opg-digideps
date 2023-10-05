const EnableJavascript = {
  init: function (document) {
    document.body.className = ((document.body.className) ? document.body.className + ' js-enabled' : 'js-enabled')
  }
}

export default EnableJavascript
