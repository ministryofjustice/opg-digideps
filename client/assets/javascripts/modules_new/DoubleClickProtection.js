const DoublClickProtection = {

    init: function () {

        document.addEventListener('click', function (e) {
            const element = e.target
            if (element.dataset.module === 'opg-toggleable-submit') {

                element.classList.add(
                    'opg-submit-link--disabled',
                    'govuk-button--disabled'
                )
                element.disabled = true

                setTimeout(function () {
                    element.classList.remove(
                        'opg-submit-link--disabled',
                        'govuk-button--disabled'
                    )
                    element.disabled = false
                }, 3000)
            }
        })
    }
}

export default DoublClickProtection

