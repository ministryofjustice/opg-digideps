import { describe, expect, it, jest } from '@jest/globals'
import DetailsExpander from '../../modules_new/DetailsExpander'

describe('Details expander', () => {
    it('should toggle js-hidden on a numeric value being input', () => {
        
        document.body.innerHTML = '<div class="js-details-expander">'+
        '<div id="group" class="govuk-form-group ">' +
        '<label for="costs" class="govuk-label ">Other</label>'+
        '<input type="text" id="costs" name="costs" required="required" class="govuk-input govuk-!-width-one-quarter js-format-currency" rows="5">'+
        '</div>'+
        '<div id="detail" class="govuk-form-group opg-indented-block js-hidden js-details-expandable">'+
        '<label for="detail" class="govuk-label">Details</label>'+
        '<textarea id="details" name="details" required="required" class="govuk-textarea  govuk-!-width-one-half " rows="5"></textarea>'+
        '</div></div>'

        DetailsExpander.init(document, '.js-details-expander');
        const input=document.querySelector('input[type=text]')
        const detail = document.getElementById('detail');

        expect(detail.classList.contains('js-hidden')).toBe(true)

        input.value = '13.00'
        const event = new InputEvent('input');
        input.dispatchEvent(event);

        expect(detail.classList.contains('js-hidden')).toBe(false)

        input.value = ''
        const new_event = new InputEvent('input');
        input.dispatchEvent(new_event);

        expect(detail.classList.contains('js-hidden')).toBe(true)


    })
})