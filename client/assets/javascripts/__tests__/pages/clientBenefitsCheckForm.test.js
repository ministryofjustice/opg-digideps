import { describe, it } from '@jest/globals'
import ConditionalFieldRevealer from '../../modules/fieldClearer'

require('../../pages/clientBenefitsCheckForm')

describe('Client Benefits Check', () => {
  const setBody = () => {
    document.body.innerHTML = `
<form data-module="clear-other-inputs">
    <input id="1" value="abc123">
    <input id="2">
</form>
<form>
    <input id="3" value="xzx654">
    <input id="4">
</form>
    `
  }

  it('adds event listeners to window to listen to keydown events', () => {
    setBody()

    const windowAELSpy = jest.spyOn(document, 'addEventListener')

    document.dispatchEvent(new window.Event('DOMContentLoaded', {
      bubbles: true,
      cancelable: true
    }))

    expect(windowAELSpy).toHaveBeenCalledWith('keydown', ConditionalFieldRevealer.onKeydownHandler)
  })

  it('removes values from all sibling input elements of the element that emitted the event', () => {
    setBody()

    const input1 = document.getElementById('1')
    const input2 = document.getElementById('2')

    document.dispatchEvent(new window.Event('DOMContentLoaded', {
      bubbles: true,
      cancelable: true
    }))

    const keyDownEvent = new window.KeyboardEvent('keydown', { key: 'e', bubbles: true })

    input2.dispatchEvent(keyDownEvent)

    expect(input1.value).toEqual(null)
    expect(input2.value).toEqual('e')
  })
})
