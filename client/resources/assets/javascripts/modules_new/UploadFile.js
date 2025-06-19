const UPLOAD_LIMIT = 15
const uploadFile = {
  init: function (document) {
    document.addEventListener('click', function (event) {
      if (event.target.getAttribute('id') === 'report_document_upload_files') {
        const filename = event.target.value
        if (filename) {
          document.getElementById('upload-progress')?.classList.remove('hidden')
        }

        const fileElement = document.getElementById('report_document_upload_files')
        const form = document.getElementById('upload_form_post_submission')

        fileElement.addEventListener('change', function () {
          if (event.target.getAttribute('id') === 'upload_form_post_submission') {
            const actionUrl = event.target.getAttribute('action')
            const fsize = fileElement.files[0].size

            if (fsize > UPLOAD_LIMIT * 1024 * 1024) {
              window.location = actionUrl + '?error=tooBig'
            }
          }

          if (fileElement.files.length > 0) {
            form.submit()
          }
        })
      }
    })

    document.addEventListener('submit', function (event) {
      if (event.target.getAttribute('id') === 'upload_form') {
        event.preventDefault()
        const fileElement = document.getElementById('report_document_upload_files')
        const actionUrl = event.target.getAttribute('action')

        // check whether browser fully supports all File API
        // TODO refactor to not use redirect for error
        if (
          window.File &&
                window.FileReader &&
                window.FileList &&
                window.Blob &&
                fileElement.files.length > 0
        ) {
          const fsize = fileElement.files[0].size
          if (fsize > UPLOAD_LIMIT * 1024 * 1024) {
            window.location = actionUrl + '?error=tooBig'
            return
          }
        }
        event.target.submit()
      }
    })
  }

}
export default uploadFile
