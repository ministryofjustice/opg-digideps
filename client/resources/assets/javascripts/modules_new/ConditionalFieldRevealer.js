const ConditionalFieldRevealer = {
  init: function () {
    const elementsWithValues = ConditionalFieldRevealer.getConditionallyHiddenElements()

    elementsWithValues.forEach(e => {
      const textInputs = [e.querySelector('input'), e.querySelector('textarea')]

      textInputs.forEach(ti => {
        if (ti && e && 'value' in ti && ti.value.length > 0) {
          e.classList.remove('govuk-radios__conditional--hidden')
        }
      })
    })
  },

  getConditionallyHiddenElements: function () {
    return [...document.querySelectorAll('.govuk-radios__conditional--hidden')]
  }

}

export default ConditionalFieldRevealer
