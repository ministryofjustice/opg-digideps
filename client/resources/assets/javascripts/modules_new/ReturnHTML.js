module.exports = {
  init: function (document) {
    // return HTML and inject in place of link
    const that = this
    document.addEventListener('click', function (event) {
      const el = event.target
      if (el.matches('a.js-return-html')) {
        event.preventDefault()
        event.stopPropagation()
        const href = el.getAttribute('href')
        that.request(href, el)
      }
    })
  },
  request: function (href, element) {
    return fetch(href).then((response) => {
      if (response.ok) {
        return response.text()
      }
    }).then((text) => {
      element.outerHTML = text
    }).catch((err) => {
      console.log(err)
    })
  }
}
