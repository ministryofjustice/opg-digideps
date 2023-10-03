import { describe, expect, it } from '@jest/globals'
import TableMultiSelect from '../../modules_new/TableMultiSelect'

const TEMPLATE = '<input type="checkbox" data-js="multiselect-checkbox-all" id="checkall">' +
'<button data-js="multiselect-disabled-button" id="bt1">Act</button>' +
'<span data-js="multiselect-selected-count" id="sp1">0 cases</span> selected' +
'<table>' +
'<tr><input type="checkbox" data-js="multiselect-checkbox" id="cb1"></tr>' +
'<tr><input type="checkbox" data-js="multiselect-checkbox" id="cb2"></tr>' +
'<tr><input type="checkbox" data-js="multiselect-checkbox" id="cb3"></tr>' +
'</table>'

describe('Checkbox multi-select', () => {
  it('Should allow select all on a group of checkboxes', () => {
    document.body.innerHTML = TEMPLATE

    TableMultiSelect.init(document)

    const all = document.getElementById('checkall')
    const text = document.getElementById('sp1')

    all.click()

    expect(document.querySelectorAll('[data-js="multiselect-checkbox"]:checked').length).toBe(3)
    expect(text.textContent).toBe('3 cases')
  })

  it('Should keep count updated as checkboxes change and update enabled actions', () => {
    document.body.innerHTML = TEMPLATE

    TableMultiSelect.init(document)

    const all = document.getElementById('checkall')
    const cb1 = document.getElementById('cb1')
    const cb2 = document.getElementById('cb2')
    const cb3 = document.getElementById('cb3')

    const text = document.getElementById('sp1')
    const btn = document.getElementById('bt1')

    all.click()
    cb1.click()

    expect(text.textContent).toBe('2 cases')
    expect(btn.disabled).toBe(false)

    cb2.click()

    expect(text.textContent).toBe('1 case')
    expect(btn.disabled).toBe(false)

    cb3.click()

    expect(text.textContent).toBe('0 cases')
    expect(btn.disabled).toBe(true)

    cb2.click()

    expect(text.textContent).toBe('1 case')
    expect(btn.disabled).toBe(false)
  })

  it('Should keep checkbox all updated based on other checkboxes state', () => {
    document.body.innerHTML = TEMPLATE

    TableMultiSelect.init(document)

    const all = document.getElementById('checkall')
    const cb1 = document.getElementById('cb1')
    const cb2 = document.getElementById('cb2')
    const cb3 = document.getElementById('cb3')
    const text = document.getElementById('sp1')

    all.click()
    expect(text.textContent).toBe('3 cases')
    expect(document.querySelectorAll('[data-js="multiselect-checkbox"]:checked').length).toBe(3)

    all.click()
    expect(text.textContent).toBe('0 cases')
    expect(document.querySelectorAll('[data-js="multiselect-checkbox"]:checked').length).toBe(0)
    expect(all.checked).toBe(false)

    cb1.click()
    cb2.click()
    cb3.click()

    expect(text.textContent).toBe('3 cases')
    expect(document.querySelectorAll('[data-js="multiselect-checkbox"]:checked').length).toBe(3)
    expect(all.checked).toBe(true)
  })
})
