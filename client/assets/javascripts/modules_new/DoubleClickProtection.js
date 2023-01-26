const DoublClickProtection = {

  init: function (listeningElement) {
    listeningElement.addEventListener('click', function (e) {
      const element = e.target
      if (element.dataset.module === 'opg-toggleable-submit') {
        element.classList.add(
          'opg-submit-link--disabled',
          'govuk-button--disabled'
        )
        setTimeout(function () {
          element.classList.remove(
            'opg-submit-link--disabled',
            'govuk-button--disabled'
          )
        }, 3000)
      }
    })
  }
}

export default DoublClickProtection
