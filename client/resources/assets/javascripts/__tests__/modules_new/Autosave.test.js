import { beforeEach, describe, expect, it } from '@jest/globals'
import fetchMock from 'jest-fetch-mock'
import Autosave from '../../modules_new/Autosave'

fetchMock.enableMocks()

describe('Form autosave', () => {
  beforeEach(() => {
    fetchMock.resetMocks()

    document.body.innerHTML = `
      <form class="js-autosave" action="/save/form">
        <input type="text" name="field1">
        <input type="text" name="field2" class="js-autosave-ignore">
        <button class="js-autosave-save-progress-button">Save progress</button>
      </form>
    `
  })

  it('should save even if change events are not triggered', async () => {
    fetchMock.mockResponse(JSON.stringify({ status: 200 }))

    const autosaver = Autosave.init(document, 0.1, fetchMock)

    // add a timer to stop the autosave on the form from running for too long
    const form = autosaver.autosaveForms[0]

    await new Promise((resolve) => setTimeout(() => {
      clearInterval(parseInt(form.getAttribute('autosave-interval-id')))
      resolve()
    }, 200))

    expect(fetchMock).toHaveBeenCalled()

    // check args passed to fetch
    const [url, options] = fetchMock.mock.calls[0]

    expect(url).toContain('/save/form')
    expect(options.method).toBe('POST')
    expect(options.body).toBeInstanceOf(FormData)
  })
})
