import { describe, it } from '@jest/globals'
import ConditionalFieldRevealer from '../../modules/ConditionalFieldRevealer'

describe('ConditionalFieldRevealer', () => {
  const setBody = function () {
    document.body.innerHTML = `
<form>
    <div class="govuk-radios__conditional govuk-radios__conditional--hidden expected">
        <input id="1" value="abc123">
    </div>
    <div class="govuk-radios__conditional govuk-radios__conditional--hidden expected">
        <input id="2" value="xyz456">
    </div>
    <div class="govuk-radios__conditional govuk-radios__conditional--hidden expected">
        <input id="3">
    </div>
    <div class="govuk-radios__conditional not-expected">
        <input id="4" value="zzz789">
    </div>
</form>
    `
  }

  describe('getClearOtherInputsElements', () => {
    it('gets elements that have a class of .govuk-radios__conditional--hidden', () => {
      setBody()

      const expectedDivs = [...document.querySelectorAll('.expected')]
      const expectedMissingDivs = [...document.querySelectorAll('.not-expected')]
      const actualDivs = ConditionalFieldRevealer.getConditionallyHiddenElements()

      expectedDivs.forEach(e => {
        expect(actualDivs).toContain(e)
      })

      expectedMissingDivs.forEach(e => {
        expect(actualDivs).not.toContain(e)
      })
    })
  })

  describe('init', () => {
    it('removes .govuk-radios__conditional--hidden from the child inputs with values of hidden elements', () => {
      setBody()
      ConditionalFieldRevealer.init(true)

      const expectedInputsWithClassRemoved = [document.getElementById('1'), document.getElementById('2')]
      const expectedInputsWithClassRemaining = [document.getElementById('3')]

      expectedInputsWithClassRemoved.forEach(e => {
        expect(e.parentElement.classList).not.toContain('govuk-radios__conditional--hidden')
      })

      expectedInputsWithClassRemaining.forEach(e => {
        expect(e.parentElement.classList).toContain('govuk-radios__conditional--hidden')
      })
    })
  })
})
