import { describe, expect, it, jest } from '@jest/globals'
import DoubleClickProtection from '../../modules_new/DoubleClickProtection'

describe('Double Click Protection', () => {
  it('should call set disabled on click', () => {
    document.body.innerHTML = '<div data-module="opg-toggleable-submit"><a href="#">Click once</a></div>'

    DoubleClickProtection.init(document)

    jest.useFakeTimers()
    jest.spyOn(global, 'setTimeout')

    const wrapper = document.querySelector('div')
    wrapper.click()

    expect(setTimeout).toHaveBeenCalledTimes(1)

    expect(wrapper.classList.contains('govuk-button--disabled')).toBe(true)
    expect(wrapper.classList.contains('opg-submit-link--disabled')).toBe(true)

    jest.runAllTimers()

    expect(wrapper.classList.contains('govuk-button--disabled')).toBe(false)
    expect(wrapper.classList.contains('opg-submit-link--disabled')).toBe(false)
  })
})
