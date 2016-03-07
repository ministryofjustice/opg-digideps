import {
    GET_TRANSFERS,
    UPDATE_TRANSFER,
    DELETE_TRANSFER,
    GET_TRANSFERS_ERROR,
    SAVE_TRANSFER_ERROR,
    ADD_TRANSFER
  } from '../actions/transfers_actions';
import { containsIncompleteTransfer, appendNewTransfer } from '../utils/transfer_utils';

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
            console.log('replace index:', pos);
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
        return appendNewTransfer(newState);
    }

    return newState;
}
function updateAll(state, transfers) {
    if (!containsIncompleteTransfer(transfers) || transfers.length === 0) {
        return appendNewTransfer(transfers);
    }

    return transfers;
}

function deleteItem(state, id) {
    let clonedState = state.filter(item => item.id !== id);
    return clonedState;
}

export default function(state = [], action) {
    switch (action.type) {
    case ADD_TRANSFER:
        if (action.payload.hasOwnProperty('data')
         && action.payload.data.hasOwnProperty('transfer')) {
            return updateNewWithRealId(state, action.payload.data.transfer);
        }
        break;
    case GET_TRANSFERS:
        if (action.payload.hasOwnProperty('data')
         && action.payload.data.hasOwnProperty('transfers')) {
            return updateAll(state, action.payload.data.transfers);
        }
        break;
    case UPDATE_TRANSFER:
        return update(state, action.payload);
    case DELETE_TRANSFER:
        return deleteItem(state, action.payload.id);
    case GET_TRANSFERS_ERROR:
    case SAVE_TRANSFER_ERROR:
        console.log('Error updating');
        return state;
    default:
      // Nothing
    }
    return state;
}
