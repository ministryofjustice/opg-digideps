// restrict a form to only being submittable once
const FormSingleSubmit = {
  init: function () {
    document.querySelectorAll('[data-single-submit-form="true"]').forEach((form) => {
      form.addEventListener('submit', (e) => {
        const isSubmitting = form.getAttribute('data-is-submitting')

        if (isSubmitting === null) {
          form.setAttribute('data-is-submitting', 'true')
        } else {
          e.preventDefault()
        }
      })
    })
  }
}

export default FormSingleSubmit
