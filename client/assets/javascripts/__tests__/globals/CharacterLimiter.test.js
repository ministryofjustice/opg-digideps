import { describe, it, expect, jest } from '@jest/globals'
import CharacterLimiter from '../../globals/CharacterLimiter'

describe('CharacterLimiter', () => {
  it('should limit the number of characters on the required field', () => {
    document.body.innerHTML = `
    <div class="govuk-form">
        <textarea class="govuk-textarea js-limit-chars-4" rows="5"></textarea>
    </div>`

    const textarea = document.querySelector('[class*="js-limit-chars-"]')
    const spy = jest.spyOn(textarea, 'addEventListener')

    CharacterLimiter.init()
    textarea.value = '12345'
    textarea.dispatchEvent(new Event('keyup'))

    expect(spy).toHaveBeenCalled()
    expect(textarea.value).toEqual('1234')
  })
})
