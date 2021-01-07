/* globals $, ga */
var Ga = function (options) {
  options = options || {}
  this.timeout = Object.prototype.hasOwnProperty.call(options, 'timeout') ? options.timeout : 250
}

/**
 * Allow to track downloaded in Google analytics:
 * sends GA pageview with the "href" attribute of the given element and
 * after "timeoutMs" seconds, redirects to the page
 *
 * @param JQuery selector
 * @param integer timeoutMs milliseconds
 */
Ga.prototype.trackDownloadableLink = function (element) {
  var _this = this

  element.click(function (e) {
    e.preventDefault()
    var link = $(e.target).attr('href')

    // track page view with the "href" link
    ga('send', 'pageview', link)
    // console.log("tracking GA link " + link);

    // continue to load page
    setTimeout(function () {
      window.location.href = link
    }, _this.timeout)

    return false
  })
}

module.exports = Ga
