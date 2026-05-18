import { describe, expect, it } from '@jest/globals'
import Autosave from '../../modules_new/Autosave'

describe('Form autosave', () => {
  document.body.innerHTML = `
    <form class="js-autosave" action="/save/form">
      <input type="text" name="field1">
      <input type="text" name="field2" class="js-autosave-ignore">
      <button class="js-autosave-save-progress-button">Save progress</button>
    </form>
  `

  it('should save even if change events are not triggered', () => {
    let mockFetchCalled = false

    const mockFetch = () => {
      mockFetchCalled = true

      return Promise.resolve({
        ok: true
      })
    }

    const autosaver = Autosave.init(document, mockFetch, 0.1)

    // add a timer to stop the autosave on the form from running for too long
    const form = autosaver.autosaveForms[0]
    setTimeout(() => {
      clearInterval(parseInt(form.getAttribute('autosave-interval-id')))
      expect(mockFetchCalled).toBe(true)
    }, 0.3)
  })
})
