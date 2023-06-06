import { describe, expect, it, jest } from '@jest/globals'
import FormatCurrency from '../../modules_new/FormatCurrency'

describe('Currency Formatting', () => {
  it('Should format currency if class present on input and event received', () => {
    document.body.innerHTML = '<form><input type="text" name="test" class="js-format-currency" value="1234567.8"></form>'

    // register fromatting on click event to simplify testing
    FormatCurrency.init(document, 'click')

    const input = document.querySelector('input')
    const spy = jest.spyOn(FormatCurrency, 'formatInput')

    expect(input.value).toBe('1234567.8')
    input.click()

    expect(spy).toBeCalled()
    expect(input.value).toBe('1,234,567.80')
  })
  it('Should format empty text and empty text', () => {
    const processed = FormatCurrency.formatInput('')
    expect(processed).toBe('')
  })

  it('Should not format non numbers text', () => {
    const processed = FormatCurrency.formatInput('Doh')
    expect(processed).toBe('Doh')
  })

  it('Should add trailign zeros to point values', () => {
    const processed = FormatCurrency.formatInput('10.2')
    expect(processed).toBe('10.20')
  })
})
