/* globals $ */
module.exports = function (containerSelector) {
  // if 'from' account is already selected when loading page, remove that account from the 'to'
  // (for example when only 'from' account is selected when posting form - which returns an error)
  const selectedFromAccount = $(containerSelector).val()
  if (selectedFromAccount !== '') {
    $('.js-transfer-to option[value=' + selectedFromAccount + ']').hide()
  }

  // when 'from' account dropdown changes
  $(containerSelector).on('change', function (e) {
    // show all 'to' accounts
    $('.js-transfer-to option').show()
    // selected 'from' account
    const selectedId = $(this).val()
    // only update 'to' accounts if a 'from' account is selected
    if (selectedId !== '') {
      // if 'to' account matches selected 'from' account, reset dropdown
      const $selectTo = $('.js-transfer-to')
      if ($selectTo.val() === selectedId) {
        $selectTo.val('')
      }
      // hide the selected 'from' account from the 'to' accounts list
      $('.js-transfer-to option[value=' + selectedId + ']').hide()
    }
  })
}
