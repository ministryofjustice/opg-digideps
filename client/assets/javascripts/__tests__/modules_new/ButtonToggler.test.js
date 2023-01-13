import { describe, expect, it, jest } from '@jest/globals'
import ButtonToggler from '../../modules_new/ButtonToggler'

describe('ButtonToggler', () => {
  describe('init', () => {
    it('add event listener to toggle', () => {
      document.body.innerHTML = `
                <button data-module="opg-button-toggler">Click me!</button>
            `

      const toggle = document.querySelector('[data-module="opg-button-toggler"]')
      const spy = jest.spyOn(toggle, 'addEventListener')

      ButtonToggler.init(toggle)

      expect(spy).toHaveBeenCalled()
    })

    it('adds disabled attribute to buttons', () => {
      document.body.innerHTML = `
                <button data-module="opg-button-toggler">Click me!</button>
                <button data-module="opg-toggleable-button">Click me!</button>
            `

      const toggle = document.querySelector('[data-module="opg-button-toggler"]')
      const buttons = document.querySelectorAll('[data-module="opg-toggleable-button"]')

      ButtonToggler.init(toggle)
      toggle.click()

      buttons.forEach((button) => {
        expect(button.disabled).toBeTruthy()
      })
    })
  })
})
