import {
    GET_TRANSFERS,
    UPDATE_TRANSFER,
    DELETE_TRANSFER,
    GET_TRANSFERS_ERROR,
    SAVE_TRANSFER_ERROR,
    ADDED_TRANSFER
  } from '../actions/transfers_actions';
import { containsIncompleteTransfer, appendNewTransfer, formatCurrency } from '../utils/transfer_utils';

function updateNewIncomplete(state, transfer) {
    let clonedState = state.slice(0);
    for (let pos = 0; pos < clonedState.length; pos += 1) {
        if (clonedState[pos].id === null && !clonedState[pos].waitingForId) {
            clonedState[pos] = transfer;
            break;
        }
    }

    return clonedState;
}
function updateNewCompleteWithoutId(state, transfer) {
    let clonedState = state.slice(0);
    for (let pos = 0; pos < clonedState.length; pos += 1) {
        if (clonedState[pos].id === null && clonedState[pos].waitingForId) {
            clonedState[pos] = transfer;
            break;
        }
    }

    return clonedState;
}
function regularUpdate(state, transfer) {
    let clonedState = state.slice(0);
    for (let pos = 0; pos < clonedState.length; pos += 1) {
        if (clonedState[pos].id === transfer.id) {
            clonedState[pos] = transfer;
            break;
        }
    }

    return clonedState;
}
function updateNewWithRealId(state, transfer) {
    let clonedState = state.slice(0);
    for (let pos = 0; pos < clonedState.length; pos += 1) {
        if (clonedState[pos].waitingForId) {
            clonedState[pos].id = transfer.id;
            clonedState[pos].waitingForId = false;
            break;
        }
    }

    return clonedState;
}
function update(state, transfer) {
    let newState;

    if (transfer.id === null) {
        if (transfer.waitingForId === false) {
            newState = updateNewIncomplete(state, transfer);
        } else {
            newState = updateNewCompleteWithoutId(state, transfer);
        }
    } else {
        newState = regularUpdate(state, transfer);
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
    case ADDED_TRANSFER:
        if (action.payload.hasOwnProperty('data')
         && action.payload.data.hasOwnProperty('transfer')) {
            return updateNewWithRealId(state, action.payload.data.transfer);
        }
        break;
    case GET_TRANSFERS:
        if (action.payload.hasOwnProperty('data')
         && action.payload.data.hasOwnProperty('transfers')) {
            return updateAll(state, action.payload.data.transfers, action.reportId);
        }
        break;
    case UPDATE_TRANSFER:
        return update(state, action.payload);
    case DELETE_TRANSFER:
        return deleteItem(state, action.payload.id);
    case GET_TRANSFERS_ERROR:
    case SAVE_TRANSFER_ERROR:
        return state;
    default:
      // Nothing
    }
    return state;
}
