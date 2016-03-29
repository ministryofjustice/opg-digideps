import { GET_TRANSFERS, UPDATE_TRANSFER, DELETE_TRANSFER, ADDED_TRANSFER } from '../actions/transfers_actions';
import { containsIncompleteTransfer, appendNewTransfer, formatCurrency, validateAmount } from '../utils/transfer_utils';

function updateNewWithRealId(state, transfer) {
    let newState = state.slice(0);
    for (let pos = 0; pos < newState.length; pos += 1) {
        if (newState[pos].temporaryId == transfer.temporaryId) {
            newState[pos].id = transfer.id;
            newState[pos].temporaryId = null;
            break;
        }
    }
    return newState;
}
function updateTransfer(state, transfer) {
    let newState = state.slice(0);

    validateAmount(transfer);

    for (let pos = 0; pos < newState.length; pos += 1) {
        if (transfer.id !== null && newState[pos].id === transfer.id ||
            transfer.id === null && newState[pos].temporaryId === transfer.temporaryId)
        {
            newState[pos] = transfer;
            break;
        }
    }

    if (!containsIncompleteTransfer(newState) || newState.length === 0) {
        return appendNewTransfer(newState, transfer.reportId);
    }

    return newState;
}
function updateAll(state, transfers, reportId) {
    for (let pos = 0; pos < transfers.length; pos += 1) {
        transfers[pos].amount = formatCurrency(`${transfers[pos].amount}`);
    }

    if (!containsIncompleteTransfer(transfers) || transfers.length === 0) {
        return appendNewTransfer(transfers, reportId);
    }

    return transfers;
}

function deleteItem(state, id) {
    let clonedState = state.filter(item => item.id !== id);
    return clonedState;
}

export default function(state = [], action) {
    switch (action.type) {
    case ADDED_TRANSFER: {
        if (action.payload.hasOwnProperty('data')
         && action.payload.data.hasOwnProperty('transfer')) {
            return updateNewWithRealId(state, action.payload.data.transfer);
        }
        break;
    }
    case GET_TRANSFERS:
        if (action.payload.hasOwnProperty('data')
         && action.payload.data.hasOwnProperty('transfers')) {
            return updateAll(state, action.payload.data.transfers, action.reportId);
        }
        break;
    case UPDATE_TRANSFER:
        return updateTransfer(state, action.payload);
    case DELETE_TRANSFER:
        return deleteItem(state, action.payload.id);
    }
    return state;
}
