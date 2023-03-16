module.exports = {
  init: function () {
    // if 'from' account is already selected when loading page, remove that account from the 'to'
    // (for example when only 'from' account is selected when posting form - which returns an error)

    const selectedFromAccount = document.querySelector('.js-transfer-from')

    if (selectedFromAccount) {
      document.querySelector('.js-transfer-to option[value="' + selectedFromAccount.value + '"]').classList.add('hidden')
    }

    document.addEventListener('change', function (event) {
      if (event.target.matches('select.js-transfer-from')) {
        document.querySelectorAll('.js-transfer-to option').forEach((element) => {
          element.classList.remove('hidden')
        })

        const selectedFromAccountNumber = event.target.value
        // only update 'to' accounts if a 'from' account is selected
        if (selectedFromAccountNumber !== '') {
          // if 'to' account matches selected 'from' account, reset dropdown
          const selectTo = document.querySelector('.js-transfer-to')
          if (selectTo.value === selectedFromAccountNumber) {
            selectTo.value = ''
          }
          // hide the selected 'from' account from the 'to' accounts list
          document.querySelector('.js-transfer-to option[value="' + selectedFromAccountNumber + '"]').classList.add('hidden')
        }
      }
    })
  }
}
