import { Redirector } from '../../modules/redirector'
import { describe, it } from '@jest/globals'

describe('Redirector', () => {
  const realLocation = window.location

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
      const url = 'https://www.example.org'
      Redirector(url)

      expect(window.location.href).toEqual(url)
    })
  })
})
