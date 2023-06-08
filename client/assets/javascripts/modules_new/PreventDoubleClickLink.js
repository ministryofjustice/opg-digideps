const PreventDoubleClickLink = {
  init: function (document) {
    // Get all elements with the class "single-click-link"
    const links = document.getElementsByClassName('single-click-link')

    // Loop through each link element and apply the attribute and event listener
    for (let i = 0; i < links.length; i++) {
      const link = links[i]
      // Add the click event listener to disable the link on first click
      link.addEventListener('click', function () {
        link.classList.add('disabled')
      })
    }
  }
}

export default PreventDoubleClickLink
