import { describe, expect, it } from '@jest/globals'
import DetailsExpander from '../../modules_new/DetailsExpander'

describe('Details expander', () => {
  it('should toggle js-hidden on a numeric value being input', () => {
    document.body.innerHTML = '<div class="js-details-expander">' +
        '<div id="group" class="govuk-form-group ">' +
        '<label for="costs" class="govuk-label ">Other</label>' +
        '<input type="text" id="costs" name="costs" required="required" class="govuk-input govuk-!-width-one-quarter js-format-currency" rows="5">' +
        '</div>' +
        '<div id="detail" class="govuk-form-group opg-indented-block js-hidden js-details-expandable">' +
        '<label for="detail" class="govuk-label">Details</label>' +
        '<textarea id="details" name="details" required="required" class="govuk-textarea  govuk-!-width-one-half " rows="5"></textarea>' +
        '</div></div>'

    DetailsExpander.init(document, '.js-details-expander')
    const input = document.querySelector('input[type=text]')
    const detail = document.getElementById('detail')

    expect(detail.classList.contains('js-hidden')).toBe(true)

    input.value = '13.00'
    const event = new InputEvent('input')
    input.dispatchEvent(event)

    expect(detail.classList.contains('js-hidden')).toBe(false)

    input.value = ''
    const newEvent = new InputEvent('input')
    input.dispatchEvent(newEvent)

    expect(detail.classList.contains('js-hidden')).toBe(true)
  })
})

describe('Multiple details expanders', () => {
  it('should toggle js-hidden on numeric values being input into the relevant field', () => {
    document.body.innerHTML = '<div class="js-details-expander">' +
      '<div id="form-group-fee_fees_5_amount" class="govuk-form-group ">' +
      '<label for="fee_fees_5_amount" class="govuk-label ">Travel costs</label>' +
      '<input type="text" id="fee_fees_5_amount" name="fee[fees][5][amount]" required="required" class="govuk-input govuk-!-width-one-quarter js-format-currency" rows="5">' +
      '</div>' +
      '<div id="form-group-fee_fees_5_moreDetails" class="govuk-form-group opg-indented-block hard--top js-hidden js-details-expandable">' +
      '<label for="fee_fees_5_moreDetails" class="govuk-label">Please provide some detail</label>' +
      '<textarea id="fee_fees_5_moreDetails" name="fee[fees][5][moreDetails]" required="required" class="govuk-textarea  govuk-!-width-one-half " rows="5"></textarea>' +
      '</div></div>' +
      '<div class="js-details-expander">' +
      '<div id="form-group-fee_fees_6_amount" class="govuk-form-group ">' +
      '<label for="fee_fees_6_amount" class="govuk-label ">Specialist services</label>' +
      '<input type="text" id="fee_fees_6_amount" name="fee[fees][6][amount]" required="required" class="govuk-input govuk-!-width-one-quarter js-format-currency" rows="5">' +
      '</div>' +
      '<div id="form-group-fee_fees_6_moreDetails" class="govuk-form-group opg-indented-block hard--top js-hidden js-details-expandable">' +
      '<label for="fee_fees_6_moreDetails" class="govuk-label">Please provide some detail</label>' +
      '<textarea id="fee_fees_6_moreDetails" name="fee[fees][6][moreDetails]" required="required" class="govuk-textarea  govuk-!-width-one-half " rows="5"></textarea>' +
      '</div></div>'

    DetailsExpander.init(document, '.js-details-expander')
    const firstInput = document.getElementById('fee_fees_5_amount')
    const firstDetail = document.getElementById('form-group-fee_fees_5_moreDetails')
    const secondInput = document.getElementById('fee_fees_6_amount')
    const secondDetail = document.getElementById('form-group-fee_fees_6_moreDetails')

    expect(firstDetail.classList.contains('js-hidden')).toBe(true)
    expect(secondDetail.classList.contains('js-hidden')).toBe(true)

    firstInput.value = '13.00'
    const firstEvent = new InputEvent('input')
    firstInput.dispatchEvent(firstEvent)

    expect(firstDetail.classList.contains('js-hidden')).toBe(false)
    expect(secondDetail.classList.contains('js-hidden')).toBe(true)

    secondInput.value = '13.00'
    const secondEvent = new InputEvent('input')
    secondInput.dispatchEvent(secondEvent)

    expect(firstDetail.classList.contains('js-hidden')).toBe(false)
    expect(secondDetail.classList.contains('js-hidden')).toBe(false)
  })
})
