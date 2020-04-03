class ButtonToggler {
  constructor (toggle) {
    this.toggle = toggle
  }

  init () {
    console.log('inited')
    this.toggle.addEventListener('click', () => this.toggleButtonDisabled())
  }

  toggleButtonDisabled () {
    console.log('clicked')

    const nodes = document.querySelector('[data-module="opg-toggleable-button"]')

    nodes.forEach(button => {
      const disabled = !button.disabled

      button.disabled = disabled
      button.setAttribute('aria-disabled', disabled.toString())
      button.classList.toggle('govuk-button--disabled')
    })
  }
}

export { ButtonToggler }
