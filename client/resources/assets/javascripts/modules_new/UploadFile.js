const UPLOAD_LIMIT = 15 * 1024 * 1024
const uploadFile = {
  handleChangeEvent: function (event, windowLocal) {
    if (windowLocal === 'undefined') {
      windowLocal = window
    }

    const elt = event.target

    // if not a file element or no files selected, do nothing
    if (
      elt.nodeName !== 'INPUT' ||
      elt.type !== 'file' ||
      elt.getAttribute('id') !== 'report_document_upload_files' ||
      elt.files.length < 1
    ) {
      return true
    }

    const form = elt.form

    // if not a file chooser form, do nothing
    if (
      form === 'undefined' ||
      form.nodeName !== 'FORM' ||
      form.getAttribute('data-role') !== 'file-chooser-form'
    ) {
      return true
    }

    // if any file is too large, redirect to error page
    const files = elt.files
    for (let i = 0; i < files.length; i++) {
      if (files[i].size > UPLOAD_LIMIT) {
        windowLocal.location = form.action + '?error=tooBig'
        return false
      }
    }

    // show progress
    form.querySelector('[data-role=file-chooser-form-progress]')?.classList.remove('hidden')

    form.submit()

    // don't allow file selector to be clicked again;
    // NB this has to happen *after* the submit otherwise no files are sent
    elt.disabled = 'disabled'

    return true
  },

  init: function (document) {
    // hide any submit buttons inside file chooser forms
    document.querySelectorAll('[data-role=file-chooser-form-submit]').forEach(btn => {
      btn.classList.add('visually-hidden')
    })

    document.addEventListener('change', event => {
      this.handleChangeEvent(event, window)
    })
  }
}
export default uploadFile
