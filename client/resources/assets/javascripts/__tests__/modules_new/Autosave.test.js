import { beforeEach, describe, expect, it } from '@jest/globals'
import fetchMock from 'jest-fetch-mock'
import Autosave from '../../modules_new/Autosave'

fetchMock.enableMocks()

// ensure all promises are resolved before continuing
const flushPromises = () => new Promise((resolve) => setTimeout(resolve, 0))

describe('Form autosave', () => {
  beforeEach(() => {
    fetchMock.resetMocks()

    document.body.innerHTML = `
      <form class="js-autosave" action="/save/form">
        <input type="text" name="personName">
        <textarea name="description" class="js-autosave-ignore"></textarea>
        <button class="js-autosave-save-progress-button">Save progress</button>
      </form>
    `
  })

  it('should save on form element value change', () => {
    const personName = 'Bill'

    fetchMock.mockResponse(JSON.stringify({ status: 200 }))

    Autosave.init(window, 30, fetchMock)

    // change a value in a text input; event must bubble to reach the listener
    // attached to the parent form
    const nameInput = document.querySelector('input[name="personName"]')
    nameInput.value = personName
    nameInput.dispatchEvent(new Event('change', { bubbles: true }))

    // confirm that the fetch function was called
    expect(fetchMock).toHaveBeenCalled()

    const [url, options] = fetchMock.mock.calls[0]
    expect(url).toContain('/save/form')
    expect(options.method).toBe('POST')
    expect(options.body).toBeInstanceOf(FormData)

    // check that the body contains the value of the personName field but not
    // the value of the ignored textarea
    expect(options.body.get('personName')).toBe(personName)
    expect(options.body.get('description')).toBe(null)
  })

  it('should save all fields on form submit button press', () => {
    const personName = 'Freda'
    const description = 'Some description of a person'

    fetchMock.mockResponse(JSON.stringify({ status: 200 }))

    Autosave.init(window, 30, fetchMock)

    // set a value for the personName (but don't trigger the change event)
    const nameInput = document.querySelector('input[name="personName"]')
    nameInput.value = personName

    // set the value for the textarea
    const descriptionInput = document.querySelector('textarea[name="description"]')
    descriptionInput.value = description

    // press save button
    document.querySelector('button.js-autosave-save-progress-button')
      .dispatchEvent(new Event('click'))

    // confirm that the fetch function was called twice
    expect(fetchMock).toHaveBeenCalled()

    // check the properties of the saved data, which should include the description
    const options = fetchMock.mock.calls[0][1]

    // check that the body contains the value of the personName field *and*
    // the value of the textarea (which is ignored until the save button is pressed)
    expect(options.body.get('personName')).toBe(personName)
    expect(options.body.get('description')).toBe(description)
  })

  it('should save periodically even if change events are not triggered', async () => {
    fetchMock.mockResponse(JSON.stringify({ status: 200 }))

    const autosaver = Autosave.init(window, 0.1, fetchMock)

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

  it('should redirect the user to the login page if their session has expired', async () => {
    const loginUrl = '/login'

    const redirectingFetch = async () => {
      return Promise.resolve({ redirected: true, url: loginUrl })
    }

    const mockWindow = {
      document: document,
      location: {
        href: null
      }
    }

    Autosave.init(mockWindow, 30, redirectingFetch)

    // change a value in a text input; event must bubble to reach the listener
    // attached to the parent form
    const nameInput = document.querySelector('input[name="personName"]')
    nameInput.value = 'Bill'
    nameInput.dispatchEvent(new Event('change', { bubbles: true }))

    // flush all pending promises so the async autosave callback completes
    await flushPromises()

    // confirm that the window's location was changed to the login URL
    expect(mockWindow.location.href).not.toBe(null)
    expect(mockWindow.location.href).toBe(loginUrl)
  })
})
