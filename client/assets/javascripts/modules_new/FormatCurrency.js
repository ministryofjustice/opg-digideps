module.exports = {
  init: function (document, eventName) {
    eventName = eventName || 'focusout'
    const module = this // event needs to reference the outer object and this is rebound to event context
    document.addEventListener(eventName, function (event) {
      if (event.target.tagName === 'INPUT' && event.target.classList.contains('js-format-currency')) {
        const number = event.target.value
        event.target.value = module.formatInput(number)
      }
    })
  },
  formatInput: function (number) {
    if (number.replace(/^\s+|\s+$/g, '') === '' || isNaN(number)) {
      return number
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
    return formattedStr
  }
}
