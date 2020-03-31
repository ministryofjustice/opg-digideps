class ButtonToggler {
  static toggleButton (buttonId) {
    let button = document.getElementById(buttonId)
    button.disabled = (buttonId === 'false') ? 'true' : 'false'
  }

  static addToggleEventListener (elementId, buttonId) {
      let element = document.getElementById(elementId)
      element.addEventListener('onclick', this.toggleButton(buttonId))
      // Update classes and aria-disabled too
  }
}

export default ButtonToggler
