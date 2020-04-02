import ButtonToggler from '../modules/buttonToggler'

window.addEventListener('DOMContentLoaded', () => {
    let bt = new ButtonToggler()
    bt.addToggleEventListener('confirmReview', 'edit-report-review-button')
})
