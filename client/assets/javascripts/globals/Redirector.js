const Redirector = function () {
  const redirectUrlData = document.querySelectorAll('[data-redirect-url]')

  if (redirectUrlData.length > 0) {
    redirectUrlData.forEach(element => {
      const url = element.getAttribute('data-redirect-url').trim()
      window.location.href = url
    })
  }
}

export default Redirector
