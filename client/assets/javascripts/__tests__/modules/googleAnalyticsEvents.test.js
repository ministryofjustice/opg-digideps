import GoogleAnalyticsEvents from '../../modules/googleAnalyticsEvents'

const setDocumentBody = () => {
  document.body.innerHTML = `
        <div>
            <button data-attribute="gae">1</button>
            <button data-attributettribute="gae">2</button>
        </div>
    `
}

describe('googleAnalyticsEvents', () => {
  describe('init', () => {
    it('attaches event listeners to elements with data-attributes=gae', () => {
      setDocumentBody()
      const buttons = document.querySelectorAll('button[data-attribute="gae"]')

      const spies = []

      buttons.forEach(button => {
        spies.push(jest.spyOn(button, 'addEventListener'))
      })

      GoogleAnalyticsEvents.init()

      spies.forEach(spy => {
        expect(spy).toHaveBeenCalledTimes(1)
        expect(spy).toHaveBeenCalledWith('userStartsURSection', expect.any(Function))
      })
    })
  })
})
