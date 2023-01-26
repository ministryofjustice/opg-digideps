const DoublClickProtection = {

    init: function (listening_element) {

        listening_element.addEventListener('click', function (e) {
            const element = e.target
            if (element.dataset.module === 'opg-toggleable-submit') {

                element.classList.add(
                    'opg-submit-link--disabled',
                    'govuk-button--disabled'
                )
                setTimeout(function () {
                    element.classList.remove(
                        'opg-submit-link--disabled',
                        'govuk-button--disabled'
                    )
                }, 3000)
            }
        })
    }
}

export default DoublClickProtection

