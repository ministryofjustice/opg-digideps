import { describe, expect, it, jest } from '@jest/globals'
import ReturnHTML from '../../modules_new/ReturnHTML'

describe('Return HTML inline', () => {
  it('Should return HTML inline on link click', () => {
    document.body.innerHTML = '<div><a href="/get" class="js-return-html">My text</a></div>'

    ReturnHTML.init(document)

    const spy = jest.spyOn(ReturnHTML, 'request').mockImplementation(() => {})

    document.querySelector('a').click()

    expect(spy).toBeCalled()
  })

  it('should fetch and replace owning element', async () => {
    document.body.innerHTML = '<div><a href="/get" class="js-return-html">My text</a></div>'

    const el = document.querySelector('a')
    const div = document.querySelector('div')

    global.fetch = jest.fn().mockImplementationOnce(() =>
      Promise.resolve({
        ok: true,
        text: () => Promise.resolve('Request Sent')
      })
    )

    await ReturnHTML.request('/get', el)

    expect(div.textContent).toBe('Request Sent')
  })
})
