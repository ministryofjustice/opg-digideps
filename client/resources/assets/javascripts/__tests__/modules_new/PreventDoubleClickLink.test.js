// Import the module to be tested
import { describe, expect } from '@jest/globals'
import PreventDoubleClickLink from '../../modules_new/PreventDoubleClickLink'

describe('PreventDoubleClickLink', () => {
  test('should add click event listeners to disable links on first click', () => {
    // Clear the document body
    document.body.innerHTML = ''

    // Create some links with the class "single-click-link"
    const link1 = document.createElement('a')
    link1.classList.add('single-click-link')
    document.body.appendChild(link1)

    const link2 = document.createElement('a')
    link2.classList.add('single-click-link')
    document.body.appendChild(link2)

    // Call the init function with the document object
    PreventDoubleClickLink.init(document)

    // Assertions
    expect(document.getElementsByClassName('single-click-link').length).toBe(2)

    // Simulate a click on the first link
    link1.dispatchEvent(new Event('click'))

    // Assert that the 'disabled' class was added to the first link element
    expect(link1.classList.contains('disabled')).toBe(true)
    expect(link2.classList.contains('disabled')).toBe(false)
  })
})
