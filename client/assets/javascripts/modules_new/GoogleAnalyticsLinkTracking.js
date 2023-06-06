/* globals ga */
module.exports = {
  // TODO this can probably be moved into the main GA Events class
  init: function (document, timeout) {
    document.addEventListener('click', (event) => {
      if (event.target.matches('a.js-trackDownloadLink')) {
        event.preventDefault()
        const link = event.target.getAttribute('href')

        // track page view with the "href" link
        ga('send', 'pageview', link)

        // continue to load page
        setTimeout(function () {
          window.location.href = link
        }, timeout)

        return false
      }
    })
  }
}
