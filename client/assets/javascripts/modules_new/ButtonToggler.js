const ButtonToggler = {
  init: function () {
    document.addEventListener('click', this.toggleButtonDisabled)
  },

  toggleButtonDisabled: function (event) {
    if (event.target.matches('[data-module="opg-button-toggler"]')) {
      const buttons = document.querySelectorAll('[data-module="opg-toggleable-button"]')
      buttons.forEach(function (button) {
        const disabled = !button.disabled

        button.disabled = disabled
        button.setAttribute('aria-disabled', disabled.toString())
        button.classList.toggle('govuk-button--disabled')
      })
    }
  }
}

export default ButtonToggler
