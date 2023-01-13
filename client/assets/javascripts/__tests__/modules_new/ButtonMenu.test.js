import { describe, expect, it, jest } from '@jest/globals'
import MOJButtonMenu from '../../modules_new/ButtonMenu'

describe('MOJButtonMenu', () => {
  describe('init', () => {
    it('should call ButtonMenu if there are more than 2 children', () => {
      document.body.innerHTML = `
        <div class="moj-button-menu">
          <div class="moj-button-menu__wrapper">
            <a href="#" role="button" class="govuk-button">
                Click here!
            </a>
          </div>
        </div>`

      const btnGroups = document.querySelector('.moj-button-menu')
      const spy = jest.spyOn(MOJButtonMenu, 'init')

      MOJButtonMenu.init(btnGroups)

      expect(spy).toHaveBeenCalled()
    })

    it('should NOT call ButtonMenu if there are less than 2 children', () => {
      document.body.innerHTML = `
          <div class="moj-button-menu">
            <div class="moj-button-menu__wrapper">
                Click me!
            </div>
          </div>`

      const btnGroups = document.querySelector('.moj-button-menu')
      const spy = jest.spyOn(MOJButtonMenu, 'init')

      MOJButtonMenu.init(btnGroups)

      expect(spy).toThrowError()
    })
  })
})
