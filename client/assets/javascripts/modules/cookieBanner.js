const CookieBanner = function () {
  var cookieBanner = document.querySelector('[data-module="opg-cookie-banner"]')

  if (cookieBanner === null) {
    return
  }

  // inner cookie banner
  var cookieInnerBanner = cookieBanner.querySelector('[data-module="opg-cookie-inner-banner"]')

  // accept or reject cookie messages
  var cookieAcceptBanner = cookieBanner.querySelector('[data-cookie="accept-message"]')
  var cookieRejectBanner = cookieBanner.querySelector('[data-cookie="reject-message"]')

  var cookieBtns = cookieBanner.querySelectorAll('.opg-cookies-btn')
  cookieBtns.forEach(function (button) {
    button.addEventListener('click', function (event) {
      // get the button that was targeted
      var btn = event.target

      if (btn.value === 'hide') {
        cookieBanner.setAttribute('hidden', '')
        return
      }

      var expires = new Date(new Date().getTime() + 1000 * 60 * 60 * 24 * 365).toUTCString()
      var policy = {
        essential: true
      }

      /**
       * if the button value is accept then set a cookie_policy cookie
       * with essential value as true and the usage value as true
       *
       * if the button value is reject then set a cookie_policy cookie
       * with essential value as true and the usage value as false
       */
      if (btn.value === 'accept') {
        cookieInnerBanner.setAttribute('hidden', '')
        cookieAcceptBanner.removeAttribute('hidden')

        policy.usage = true
        document.cookie = `cookie_policy=${JSON.stringify(policy)}; path=/; expires=${expires}; secure`
      } else if (btn.value === 'reject') {
        cookieInnerBanner.setAttribute('hidden', '')
        cookieRejectBanner.removeAttribute('hidden')

        policy.usage = false
        document.cookie = `cookie_policy=${JSON.stringify(policy)}; path=/; expires=${expires}; secure`
      }
    })
  })
}

export default CookieBanner
