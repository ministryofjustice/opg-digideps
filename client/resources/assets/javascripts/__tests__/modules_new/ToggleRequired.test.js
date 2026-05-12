import { describe, expect, it } from '@jest/globals'
import ToggleRequired from '../../modules_new/ToggleRequired'

describe('ToggleRequired', () => {
  it('target element should change its required status depending on trigger element value', () => {
    document.body.innerHTML = `
      <div class="js-toggle-required"
           data-optional-label-suffix=" (optional)"
           data-optional-message="Details field is optional"
           data-required-message="Details field is required if amount is provided">

          <input type="text" id="amount" class="js-toggle-required-trigger">

          <div id="target" class="js-toggle-required-target">
            <label id="label" for="details">Details</label>
            <textarea id="details"></textarea>
          </div>
      </div>
    `

    ToggleRequired.init(document)
    const input = document.getElementById('amount')
    const target = document.getElementById('target')
    const label = document.getElementById('label')
    const details = document.getElementById('details')

    // check aria-live element has been appended
    const toggleMessage = target.querySelector('span[aria-live="polite"]')
    expect(toggleMessage).not.toBeNull()
    expect(toggleMessage.classList.contains('visually-hidden')).toBe(true)

    // trigger element has no value (start state) => target element is optional
    expect(details.hasAttribute('required')).toBe(false)
    expect(label.querySelector('span').innerText).toBe(' (optional)')
    expect(toggleMessage.innerText).toBe('Details field is optional')

    // trigger element is zero => target element remains optional
    input.value = '0.0'
    let event = new InputEvent('input')
    input.dispatchEvent(event)

    expect(details.hasAttribute('required')).toBe(false)
    expect(label.querySelector('span').innerText).toBe(' (optional)')
    expect(toggleMessage.innerText).toBe('Details field is optional')

    // trigger element is greater than zero => target element value is required
    input.value = '10.00'
    event = new InputEvent('input')
    input.dispatchEvent(event)

    expect(details.hasAttribute('required')).toBe(true)
    expect(label.querySelector('span')).toBe(null)
    expect(toggleMessage.innerText).toBe('Details field is required if amount is provided')
  })
})
