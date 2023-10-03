import { describe, expect, it } from '@jest/globals'
import ShowHideContent from '../../modules_new/ShowHideContent'

describe('Show Hide Toggles', () => {
  it('Should toggle textarea on checkbox state change', () => {
    // this is used in the ndr
    document.body.innerHTML = '<div class="govuk-checkboxes__item" data-target="more-details">' +
                              '<input type="checkbox" id="income" name="name" ' +
                              ' required="required" class="govuk-checkboxes__input" value="1" >' +
                              '<label class="govuk-label govuk-checkboxes__label " ' +
                              'for="income">My Label</label></div>' +
                              '<div id="more-details" class="opg-indented-block js-hidden"></div>'

    ShowHideContent.init()

    const moreDetails = document.querySelector('#more-details')
    const checkBox = document.querySelector('input[type="checkbox"]')

    expect(moreDetails.classList.contains('js-hidden')).toBe(true)
    expect(checkBox.getAttribute('aria-controls')).toBe('more-details')
    expect(checkBox.getAttribute('aria-expanded')).toBe('false')

    checkBox.click()

    expect(moreDetails.classList.contains('js-hidden')).toBe(false)
    expect(checkBox.getAttribute('aria-expanded')).toBe('true')

    checkBox.click()
    expect(moreDetails.classList.contains('js-hidden')).toBe(true)
    expect(checkBox.getAttribute('aria-expanded')).toBe('false')
  })

  it('Should show the textara when we toggle between a yes and no answer', () => {
    // example in the financial decisions section
    document.body.innerHTML =
     '<div  data-module="govuk-radios">' +
        '<div data-target="more-detail">' +
        '    <input type="radio" id="opt1" name="action" value="yes">' +
        '   <label  for="opt1">Yes</label>' +
        '</div>                                                       ' +
        '<div >' +
        '    <input type="radio" id="opt2" name="action" value="no">' +
        '    <label class="govuk-label govuk-radios__label" for="opt2">No</label>' +
        '</div>' +
        '</div>' +
        '<div id="more-detail" class="opg-indented-block js-hidden" >' +
        '</div>'

    const yesBox = document.getElementById('opt1')
    const noBox = document.getElementById('opt2')
    const more = document.getElementById('more-detail')

    ShowHideContent.init()

    expect(more.classList.contains('js-hidden')).toBe(true)
    expect(yesBox.getAttribute('aria-controls')).toBe('more-detail')
    expect(yesBox.getAttribute('aria-expanded')).toBe('false')

    yesBox.click()

    expect(more.classList.contains('js-hidden')).toBe(false)
    expect(yesBox.getAttribute('aria-expanded')).toBe('true')

    noBox.click()

    expect(more.classList.contains('js-hidden')).toBe(true)
    expect(yesBox.getAttribute('aria-expanded')).toBe('false')
  })

  it('Should only show textbox if owning radio is clicked', () => {
    document.body.innerHTML =
     '<div data-module="govuk-radios">' +
        '<div  data-target="more-detail">' +
        '    <input type="radio" id="opt1" name="action" required="required"  value="yes">' +
        '   <label for="opt1">Yes</label>' +
        '</div>' +
        '<div >' +
        '    <input type="radio" id="opt2" name="action" required="required"  value="no">' +
        '    <label for="opt2">No</label>' +
        '</div>' +
        '</div>' +
        '<div id="more-detail" class="opg-indented-block js-hidden" >' +
        '</div>' +

        '<div  data-module="govuk-radios">' +
        '<div  data-target="more-detail-no2">' +
        '    <input type="radio" id="opt3" name="action2" required="required"  value="yes">' +
        '   <label  for="opt3">Yes</label>' +
        '</div>' +
        '<div >' +
        '    <input type="radio" id="opt4" name="action2" required="required"  value="no">' +
        '    <label class="govuk-label govuk-radios__label" for="opt4">No</label>' +
        '</div>' +
        '</div>' +
        '<div id="more-detail-no2" class="opg-indented-block js-hidden" >' +
        '</div>'

    const yesBox = document.getElementById('opt1')
    const more = document.getElementById('more-detail')
    const moreTwo = document.getElementById('more-detail-no2')

    ShowHideContent.init()

    expect(more.classList.contains('js-hidden')).toBe(true)
    expect(moreTwo.classList.contains('js-hidden')).toBe(true)
    expect(yesBox.getAttribute('aria-controls')).toBe('more-detail')
    expect(yesBox.getAttribute('aria-expanded')).toBe('false')

    yesBox.click()
    expect(yesBox.getAttribute('aria-expanded')).toBe('true')
    expect(more.classList.contains('js-hidden')).toBe(false)
    expect(moreTwo.classList.contains('js-hidden')).toBe(true)
  })
})
