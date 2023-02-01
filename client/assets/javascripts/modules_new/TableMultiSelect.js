module.exports = {
  init: function (document) {
    const checkboxes = document.querySelectorAll('[data-js="multiselect-checkbox"]')
    const disabledButtons = document.querySelectorAll('[data-js="multiselect-disabled-button"]')
    const checkboxAll = document.querySelector('[data-js="multiselect-checkbox-all"]')
    const checkedClass = 'opg-table__row--checked'

    disabledButtons.forEach((element) => {
      element.disabled = true
    })

    function updateText () {
      const selectedCount = document.querySelectorAll('[data-js="multiselect-checkbox"]:checked').length
      const caseString = selectedCount === 1 ? ' case' : ' cases'
      document.querySelector('[data-js="multiselect-selected-count"]').textContent = selectedCount + caseString
    }

    document.addEventListener('change', function (event) {
      if (event.target.tagName === 'INPUT' && event.target.dataset?.js === 'multiselect-checkbox-all') {
        const isChecked = event.target.checked
        checkboxes.forEach((element) => {
          element.checked = isChecked
          element.closest('tr')?.classList.toggle(checkedClass, isChecked)
        })
        disabledButtons.forEach((element) => {
          element.disabled = !isChecked
        })
        updateText()
      }
    })

    document.addEventListener('change', function (event) {
      if (event.target.tagName === 'INPUT' && event.target.dataset?.js === 'multiselect-checkbox') {
        const selectedCount = document.querySelectorAll('[data-js="multiselect-checkbox"]:checked').length

        event.target.closest('tr')?.classList.toggle(checkedClass)
        checkboxAll.checked = (selectedCount === checkboxes.length)

        disabledButtons.forEach((element) => {
          element.disabled = (selectedCount === 0)
        })
        updateText()
      }
    })
  }
}
