/* globals $ */
const checkedClass = 'opg-table__row--checked'

module.exports = function () {
  const $disabledButtons = $('[data-js="disabled-button"]')
  $disabledButtons.prop('disabled', true);

  // Select all checkbox change
  $('.js-checkbox-all').change(function () {
    $disabledButtons.prop('disabled', false)

    // Change all '.js-checkbox' checked status
    $('.js-checkbox').prop('checked', $(this).prop('checked'))

    // Toggle checked class on other checkboxes
    if ($(this).prop('checked')) {
      $('.js-checkbox').parents('tr').addClass(checkedClass)
    } else {
      $('.js-checkbox').parents('tr').removeClass(checkedClass)
      $disabledButtons.prop('disabled', true)
    }

    var caseString = $('.js-checkbox:checked').length === 1 ? ' case' : ' cases'
    $('#numberOfCases').text($('.js-checkbox:checked').length + caseString)
  })

  // '.js-checkbox' change
  $('.js-checkbox').change(function () {
    $disabledButtons.prop('disabled', false)

    $(this).parents('tr').toggleClass(checkedClass)

    // uncheck 'select all', if one of the listed checkbox item is unchecked
    if ($(this).prop('checked') === false) {
      // change 'select all' checked status to false
      $('.js-checkbox-all').prop('checked', false)
    }

    // check 'select all' if all checkbox items are checked
    if ($('.js-checkbox:checked').length === $('.js-checkbox').length) {
      $('.js-checkbox-all').prop('checked', true)
    } else if ($('.js-checkbox:checked').length === 0) {
      $disabledButtons.prop('disabled', true)
    }

    var caseString = $('.js-checkbox:checked').length === 1 ? ' case' : ' cases'
    $('#numberOfCases').text($('.js-checkbox:checked').length + caseString)
  })
}
