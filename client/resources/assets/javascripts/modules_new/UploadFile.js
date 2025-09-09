const UPLOAD_LIMIT = 15 * 1024 * 1024
const uploadFile = {
  init: function (document) {
    document.addEventListener("change", event => {
      const elt = event.target

      // if not a file element or no files selected, do nothing
      if (
        elt.nodeName !== "INPUT" ||
        elt.type !== "file" ||
        elt.getAttribute("id") !== "report_document_upload_files" ||
        elt.files.length < 1
      ) {
        return true
      }

      const form = elt.form

      // if not a file chooser form, do nothing
      if (
        form === "undefined" ||
        form.nodeName !== "FORM" ||
        form.getAttribute("data-role") !== "file-chooser-form"
      ) {
        return true
      }

      // if any file is too large, redirect to error page
      const files = elt.files
      for (let i = 0; i < files.length; i++) {
        if (files[i].size > UPLOAD_LIMIT) {
          window.location = form.action + "?error=tooBig"
        }
      }

      // disable the file chooser
      elt.disabled = "disabled"

      // show progress bar
      form.querySelector("[data-role=file-chooser-form-progress]")?.classList.remove("hidden")

      form.submit()

      return true
    })
  }

}
export default uploadFile
