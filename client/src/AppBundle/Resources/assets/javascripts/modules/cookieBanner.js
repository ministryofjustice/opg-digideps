/* globals $ */
module.exports = function cookieBanner () {
  var $banner = $('[data-module="opg-cookie-banner"]')
  var $acceptAll = $banner.find('[data-js="accept-all"]')

  $acceptAll.on('click', function (event) {
    var days = 365
    var expires = new Date(Date.now() + days * 864e5).toUTCString()
    var policy = {
      essential: true,
      usage: true
    }

    document.cookie = 'cookie_policy=' + JSON.stringify(policy) + '; path=/; expires=' + expires + '; secure'
    $banner.addClass('hidden')
    event.preventDefault()
  })
}
