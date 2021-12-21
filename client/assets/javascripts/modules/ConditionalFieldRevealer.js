const ConditionalFieldRevealer = {
  init: () => {
    const elementsWithValues = ConditionalFieldRevealer.getConditionallyHiddenElements()

    elementsWithValues.forEach(e => {
      const textInputs = [e.querySelector('input'), e.querySelector('textarea')]

      textInputs.forEach(ti => {
        if (ti && e && 'value' in ti && ti.value.length > 0) {
          console.log('Removing hidden')
          console.log(e)
          console.log(e.classList)
          e.classList.remove('govuk-radios__conditional--hidden')
        }
      })
    })
  },

  getConditionallyHiddenElements: () => {
    return [...document.querySelectorAll('.govuk-radios__conditional--hidden')]
  }

}

export default ConditionalFieldRevealer
