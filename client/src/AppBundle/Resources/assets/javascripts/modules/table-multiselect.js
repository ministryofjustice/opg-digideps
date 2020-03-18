/* globals $ */
const checkedClass = 'opg-table__row--checked'

module.exports = function () {
  const $checkboxAll = $('[data-js="multiselect-checkbox-all"]')
  const $checkboxes = $('[data-js="multiselect-checkbox"]')
  const $selectedCounter = $('[data-js="multiselect-selected-count"]')
  const $disabledButtons = $('[data-js="multiselect-disabled-button"]')

  $disabledButtons.prop('disabled', true)

  function updateText () {
    const selectedCount = $checkboxes.filter(':checked').length
    const caseString = selectedCount === 1 ? ' case' : ' cases'
    $selectedCounter.text(selectedCount + caseString)
  }

  // Select all checkbox change
  $checkboxAll.change(function () {
    const isChecked = $checkboxAll.prop('checked')

    $checkboxes.prop('checked', isChecked)
    $checkboxes.parents('tr').toggleClass(checkedClass, isChecked)
    $disabledButtons.prop('disabled', !isChecked)

    updateText()
  })

  // Individual checkbox change
  $checkboxes.change(function (e) {
    const $currentCheckbox = $(e.currentTarget)
    const selectedCount = $checkboxes.filter(':checked').length

    $currentCheckbox.parents('tr').toggleClass(checkedClass)
    $disabledButtons.prop('disabled', selectedCount === 0)
    $checkboxAll.prop('checked', selectedCount === $checkboxes.length)

    updateText()
  })
}
