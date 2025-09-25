import { describe, expect, it } from '@jest/globals'
import UploadFile from '../../modules_new/UploadFile'

describe('Upload file', () => {
  it('should hide file chooser submit buttons', () => {
    document.body.innerHTML = '<button data-role="file-chooser-form-submit">'

    UploadFile.init(document)

    const btn = document.querySelector('[data-role=file-chooser-form-submit]')
    expect(btn.classList).toContain('visually-hidden')
  })

  it('should ignore change events on non-file inputs', () => {
    document.body.innerHTML = '<input type="text">'

    // stub event
    const event = { target: document.querySelector('input') }

    expect(UploadFile.handleChangeEvent(event)).toBe(true)
  })

  it('should ignore change events on file inputs outside a file chooser form', () => {
    document.body.innerHTML = `
      <!-- file input inside a form which is not a file chooser form -->
      <form>
        <input type="file" id="input1">
      </form>

      <!-- file input outside a form -->
      <div>
        <input type="file" id="input2">
      </div>
    `

    const event1 = { target: document.querySelector('#input1') }
    expect(UploadFile.handleChangeEvent(event1)).toBe(true)

    const event2 = { target: document.querySelector('#input2') }
    expect(UploadFile.handleChangeEvent(event2)).toBe(true)
  })

  it('should redirect to the error page if selected file is too big', () => {
    // stub the window object so we can check its location value after the file change event
    const windowLocal = {}

    document.body.innerHTML = `
      <form data-role="file-chooser-form" action="https://fake.url/">
      </form>
    `

    // stub a file input element with a 20MB file
    const fileInput = {
      files: [{ size: 20 * 1024 * 1024 }],
      form: document.querySelector('form'),
      nodeName: 'INPUT',
      type: 'file',
      getAttribute: () => 'report_document_upload_files'
    }

    const event = { target: fileInput }
    expect(UploadFile.handleChangeEvent(event, windowLocal)).toBe(false)

    expect(windowLocal.location).toBe('https://fake.url/?error=tooBig')
  })

  it('should submit file and show upload progress', () => {
    document.body.innerHTML = `
      <form data-role="file-chooser-form">
        <!-- progress element; NB this has to be inside the form -->
        <div data-role="file-chooser-form-progress" class="hidden"></div>
      </form>
    `

    const form = document.querySelector('form')

    // spy on the form's submit method so we can check it is called
    const submitFnSpy = jest.spyOn(form, 'submit').mockImplementation()

    const progress = document.querySelector('[data-role=file-chooser-form-progress]')

    // stub the input element which is the target of the event
    const fileInput = {
      files: [{ size: 1024 }, { size: 2048 }],
      form: form,
      nodeName: 'INPUT',
      type: 'file',
      getAttribute: () => 'report_document_upload_files'
    }

    const event = { target: fileInput }
    expect(UploadFile.handleChangeEvent(event)).toBe(true)

    expect(fileInput.disabled).toBe('disabled')
    expect(submitFnSpy).toHaveBeenCalled()
    expect(progress.classList).not.toContain('hidden')
  })
})
