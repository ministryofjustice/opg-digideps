const ButtonToggler = {
  init: function (toggle) {
    toggle.addEventListener('click', () => this.toggleButtonDisabled())
  },

  toggleButtonDisabled: function () {
    const buttons = document.querySelectorAll('[data-module="opg-toggleable-button"]')
    buttons.forEach(function (button) {
      const disabled = !button.disabled

      button.disabled = disabled
      button.setAttribute('aria-disabled', disabled.toString())
      button.classList.toggle('govuk-button--disabled')
    })
  }
}

export default ButtonToggler
