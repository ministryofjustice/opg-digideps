const KEY_ENTER = 13
const KEY_SPACE = 32

function DetachedDetails($module) {
  this.$module = $module
  this.$summary = $module.querySelector('.govuk-details__summary')
  this.$content = document.querySelector(
    '#' + $module.getAttribute('aria-controls')
  )
}

// Initialize component
DetachedDetails.prototype.init = function () {
  // Check for module
  if (!this.$module) {
    return
  }

  this.handleInputs(this.$summary, this.handleClick.bind(this))

  this.draw()
}

DetachedDetails.prototype.handleClick = function (e) {
  e.preventDefault()

  if (this.$module.getAttribute('open') === null) {
    this.$module.setAttribute('open', 'open')
  } else {
    this.$module.removeAttribute('open')
  }

  this.draw()
}

// Update visual status of elements
DetachedDetails.prototype.draw = function () {
  const isOpen = this.$module.getAttribute('open') !== null

  this.$summary.setAttribute('aria-expanded', isOpen ? 'true' : 'false')
  this.$content.setAttribute('aria-hidden', isOpen ? 'false' : 'true')

  this.$content.style.display = isOpen ? '' : 'none'
}

/**
 * Handle cross-modal click events
 * @param {object} node element
 * @param {function} callback function
 */
DetachedDetails.prototype.handleInputs = function (node, callback) {
  node.addEventListener('keypress', function (event) {
    const target = event.target
    // When the key gets pressed - check if it is enter or space
    if (event.keyCode === KEY_ENTER || event.keyCode === KEY_SPACE) {
      if (target.nodeName.toLowerCase() === 'summary') {
        // Prevent space from scrolling the page
        // and enter from submitting a form
        event.preventDefault()
        // Click to let the click event do all the necessary action
        if (target.click) {
          target.click()
        } else {
          // except Safari 5.1 and under don't support .click() here
          callback(event)
        }
      }
    }
  })

  // Prevent keyup to prevent clicking twice in Firefox when using space key
  node.addEventListener('keyup', function (event) {
    const target = event.target
    if (event.keyCode === KEY_SPACE) {
      if (target.nodeName.toLowerCase() === 'summary') {
        event.preventDefault()
      }
    }
  })

  node.addEventListener('click', callback)
}

module.exports = DetachedDetails
