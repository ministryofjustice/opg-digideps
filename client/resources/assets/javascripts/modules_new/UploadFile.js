const UPLOAD_LIMIT = 15
const uploadFile = {
  init: function (document) {
    document.addEventListener('click', function (event) {
      if (event.target.getAttribute('id') === 'report_document_upload_files') {
        const filename = event.target.value
        if (filename) {
          document.getElementById('upload-progress')?.classList.remove('hidden')
        }

        // finds the choose file field on the form
        const fileElement = document.getElementById('report_document_upload_files');

        // finds the entire upload form
        const form = document.getElementById('upload_form_post_submission');

        //listener waits for when the user to select a file/s (change event)
        fileElement.addEventListener('change', function () {
          // alert('File selected!');
          if (event.target.getAttribute('id') === 'upload_form_post_submission') {
            event.preventDefault()

            const actionUrl = event.target.getAttribute('action')

            const fsize = fileElement.files[0].size

            if (fsize > UPLOAD_LIMIT * 1024 * 1024) {
              window.location = actionUrl + '?error=tooBig'
            }
          }

          //checks if at least one file is picked, if yes then submit for form
          if (fileElement.files.length > 0) {
            form.submit();
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
        // alert('4');
        event.target.submit()
      }
    })

  }

}
export default uploadFile
