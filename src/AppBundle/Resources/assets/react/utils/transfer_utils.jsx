const decimalplaces = 2,
    decimalcharacter = '.',
    thousandseparater = ',';



export function appendNewTransfer(transfers, reportId) {
    const d = new Date();
    const n = d.getTime();

    return [...transfers, {
        reportId: reportId,
        id: null,
        temporaryId: n,
        accountFrom: null,
        accountTo: null,
        amount: null
    }];
}

export function completeTransfer(transfer) {
    if (transfer.accountFrom !== null &&
      transfer.accountTo !== null &&
      transfer.amount !== 0 &&
      transfer.amount !== null) {

        return true;

    }

    return false;
}

export function containsIncompleteTransfer(transfers) {
    let incomplete = false;
    let pos = transfers.length - 1;

    while (pos > -1) {
        if (!completeTransfer(transfers[pos])) {
            incomplete = true;
        }
        pos -= 1;
    }

    return incomplete;
}

export function formatCurrency(number) {
    if (number.replace(/^\s+|\s+$/g, '') === '' || isNaN(number)) {
        return number;
    }

    number = parseFloat(number);

    var formatted = String(number.toFixed(decimalplaces));
    if (decimalcharacter.length && decimalcharacter != '.' ) {
        formatted = formatted.replace(/\./, decimalcharacter);
    }

    let integer = '',
        fraction = '',
        strnumber = String(formatted),
        dotpos = decimalcharacter.length ? strnumber.indexOf(decimalcharacter) : -1,
        temparray = [];

    if (dotpos > -1) {
        if (dotpos) {
            integer = strnumber.substr(0, dotpos);
        }
        fraction = strnumber.substr(dotpos + 1);
    } else {
        integer = strnumber;
    }

    if (integer) {
        integer = String(Math.abs(integer));
    }

    while (fraction.length < decimalplaces) {
        fraction += '0';
    }

    while (integer.length > 3) {
        temparray.unshift(integer.substr(-3));
        integer = integer.substr(0, integer.length - 3);
    }

    temparray.unshift(integer);
    integer = temparray.join(thousandseparater);

    let formattedStr = integer + decimalcharacter + fraction;
    if (number < 0) {
        formattedStr = '-' + formattedStr;
    }

    return formattedStr;
}

export function validateAmount(transfer) {
    let value = transfer.amount;

    if (value === null) {
        transfer.error = false;
        return;
    }

    let valueCopy = value.replace(/^\s+|\s+$/g, '');
    valueCopy = valueCopy.replace(',', '');

    if (valueCopy === '' || isNaN(valueCopy)) {
        transfer.error = 'The amount must be a number';
    } else if (parseFloat(valueCopy) === 0.00) {
        transfer.error = 'The amount must be more than 0';
    } else if (parseFloat(valueCopy) < 0.00) {
        transfer.error = 'The amount can’t be a minus number';
    } else {
        transfer.error = false;
    }
}
