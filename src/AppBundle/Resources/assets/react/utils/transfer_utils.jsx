export function appendNewTransfer(transfers, reportId) {
    return [...transfers, {
        reportId: reportId,
        id: null,
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
