import ButtonToggler from '../modules/buttonToggler'

window.addEventListener('DOMContentLoaded', () => {
  const bt = new ButtonToggler()
  bt.addToggleButtonEventListener('confirmReview', 'edit-report-button')
})
