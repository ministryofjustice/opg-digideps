// Import the module to be tested
import PreventDoubleClick from '../../modules_new/PreventDoubleClick'
import { describe, expect, jest } from '@jest/globals'

// Mock the document object
const mockDocument = {
  getElementsByClassName: jest.fn().mockReturnValueOnce([
    {
      setAttribute: jest.fn()
    },
    {
      setAttribute: jest.fn()
    }
  ])
}

// Test the init function
describe('PreventDoubleClick', () => {
  test('should add the data-prevent-double-click attribute to all buttons', () => {
    PreventDoubleClick.init(mockDocument)

    expect(mockDocument.getElementsByClassName).toHaveBeenCalledWith('govuk-button')

    expect(mockDocument.getElementsByClassName().setAttribute).toHaveBeenNthCalledWith(
      1,
      'data-prevent-double-click',
      'true'
    )

    expect(mockDocument.getElementsByClassName().setAttribute).toHaveBeenNthCalledWith(
      2,
      'data-prevent-double-click',
      'true'
    )
  })
})
