import MOJFrontend from '@ministryofjustice/frontend/moj/all.js'

class MOJButtonMenu {
  init (buttonGroups) {
    if (buttonGroups.find('.moj-button-menu__wrapper').children().length > 2) {
      const $bm = new MOJFrontend.ButtonMenu({
        container: buttonGroups,
        mq: '(min-width: 200em)',
        buttonText: 'Actions',
        buttonClasses: 'govuk-button--secondary moj-button-menu__toggle-button--secondary'
      })

      $bm()
    }
  }
}

export { MOJButtonMenu }
