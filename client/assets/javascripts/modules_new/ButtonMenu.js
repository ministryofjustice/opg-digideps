import { ButtonMenu } from '@ministryofjustice/frontend'

const MOJButtonMenu = {
  init: function (btnGroups) {
    btnGroups.forEach(btn => {
      if (btn.querySelector(':scope > .moj-button-menu__wrapper').children.length > 2) {
        const btnMenu = new ButtonMenu({
          container: btn,
          mq: '(min-width: 200em)',
          buttonText: 'Actions',
          buttonClasses: 'govuk-button--secondary moj-button-menu__toggle-button--secondary'
        })

        btnMenu()
      }
    })
  }
}

export default MOJButtonMenu
