import Redirector from '../../globals/redirector'
import { describe, it, jest, expect, afterEach, beforeEach } from '@jest/globals'

describe('Redirector', () => {
  const realLocation = window.location
  const url = 'http://www.example.com'
  const validDocumentBody = () => {
    document.body.innerHTML = `
    <div data-redirect-url="${url}">
      <p class="govuk-body govuk-!-padding-top-6 govuk-!-padding-bottom-1">
        Downlink Link!
      </p>
    </div>
      `
  }

  afterEach(() => {
    Object.defineProperty(window, 'location', {
      writable: false,
      value: { assign: realLocation }
    })
  })

  beforeEach(() => {
    Object.defineProperty(window, 'location', {
      writable: true,
      value: { assign: jest.fn() }
    })
  })

  describe('when invoked with an argument', () => {
    it('sets the document href to the value of the argument', () => {
      validDocumentBody()
      Redirector()

      expect(window.location.href).toEqual(url)
    })
  })
})
