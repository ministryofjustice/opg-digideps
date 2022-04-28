/* globals $ */
module.exports = function (element) {
  element = $(element)
  let number = element.val()

  if (number.replace(/^\s+|\s+$/g, '') === '' || isNaN(number)) {
    return
  }

  const decimalplaces = 2
  const decimalcharacter = '.'
  const thousandseparater = ','
  number = parseFloat(number)

  const negative = number < 0

  let formatted = String(number.toFixed(decimalplaces))
  if (decimalcharacter.length && decimalcharacter !== '.') {
    formatted = formatted.replace(/\./, decimalcharacter)
  }
  let integer = ''
  let fraction = ''
  const strnumber = String(formatted)
  const dotpos = decimalcharacter.length
    ? strnumber.indexOf(decimalcharacter)
    : -1
  if (dotpos > -1) {
    if (dotpos) {
      integer = strnumber.substr(0, dotpos)
    }
    fraction = strnumber.substr(dotpos + 1)
  } else {
    integer = strnumber
  }
  if (integer) {
    integer = String(Math.abs(integer))
  }
  while (fraction.length < decimalplaces) {
    fraction += '0'
  }
  const temparray = []

  while (integer.length > 3) {
    temparray.unshift(integer.substr(-3))
    integer = integer.substr(0, integer.length - 3)
  }

  temparray.unshift(integer)
  integer = temparray.join(thousandseparater)

  let formattedStr = integer + decimalcharacter + fraction
  if (negative) {
    formattedStr = '-' + formattedStr
  }

  element.val(formattedStr)
}
