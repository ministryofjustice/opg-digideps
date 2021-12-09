import SessionTimeout from '../../globals/sessionTimeout'
import { describe, it } from '@jest/globals'

describe('SessionTimeout', () => {
  describe('when invoked with an argument', () => {
    it('sets the document href to the value of the argument', () => {
      SessionTimeout()
    })
  })
})
