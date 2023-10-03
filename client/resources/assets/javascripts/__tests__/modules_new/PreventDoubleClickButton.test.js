// Import the module to be tested
import { describe, expect } from '@jest/globals'
import PreventDoubleClickButton from '../../modules_new/PreventDoubleClickButton'

// Test the init function
describe('PreventDoubleClick', () => {
  test('should add the data-prevent-double-click attribute to all buttons', () => {
    // Clear the document body
    document.body.innerHTML = ''

    // Create some buttons with the class "govuk-button"
    const button1 = document.createElement('button')
    button1.classList.add('govuk-button')
    document.body.appendChild(button1)

    const button2 = document.createElement('button')
    button2.classList.add('govuk-button')
    document.body.appendChild(button2)

    // Call the init function
    PreventDoubleClickButton.init(document)

    // Assert that the buttons have the "data-prevent-double-click" attribute
    expect(button1.getAttribute('data-prevent-double-click')).toBe('true')
    expect(button2.getAttribute('data-prevent-double-click')).toBe('true')
  })
})
