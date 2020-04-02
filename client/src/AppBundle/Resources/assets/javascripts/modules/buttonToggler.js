class ButtonToggler {
  toggleButtonDisabled (buttonId) {
    const button = document.getElementById(buttonId)
    const disabled = button.disabled ? false : true

    button.disabled = disabled
    button.setAttribute('aria-disabled', disabled.toString())
    button.classList.toggle("govuk-button--disabled")
  }

  addToggleEventListener (elementId, buttonId) {
    const element = document.getElementById(elementId)
    element.addEventListener('click', () => this.toggleButtonDisabled(buttonId))
  }
}

export default ButtonToggler
