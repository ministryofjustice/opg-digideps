class ButtonToggler {
  init (toggle) {
    toggle.addEventListener('click', () => this.toggleButtonDisabled())
  }

  toggleButtonDisabled () {
    const buttons = document.querySelectorAll('[data-module="opg-toggleable-button"]')

    buttons.forEach(button => {
      const disabled = !button.disabled

      button.disabled = disabled
      button.setAttribute('aria-disabled', disabled.toString())
      button.classList.toggle('govuk-button--disabled')
    })
  }
}

export { ButtonToggler }
