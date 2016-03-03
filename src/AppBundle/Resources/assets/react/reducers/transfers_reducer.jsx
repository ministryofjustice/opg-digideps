import {
    GET_TRANSFERS,
    UPDATE_TRANSFER,
    DELETE_TRANSFER,
    GET_TRANSFERS_ERROR,
    UPDATE_TRANSFERS_ERROR
  } from '../actions/transfers_actions';
import { containsIncompleteTransfer, appendNewTransfer } from '../utils/transfer_utils';

function update(state, transfer) {
    // If this is an update to a new transfer that is not yet saved
    let clonedState = state.slice(0);
    for (let pos = 0; pos < clonedState.length; pos += 1) {
        if (clonedState[pos].id === transfer.id) {
            clonedState[pos] = transfer;
            break;
        }
    }

    if (!containsIncompleteTransfer(clonedState) || clonedState.length === 0) {
        clonedState = appendNewTransfer(clonedState);
    }

    return clonedState;
}
function updates(state, transfers) {
    if (!containsIncompleteTransfer(transfers) || transfers.length === 0) {
        return appendNewTransfer(transfers);
    }

    return transfers;
}

function deleteItem(state, id) {
    state.filter(function(item) {
        if (item.id === id) {
            return false;
        }
        return true;
    });
}

export default function(state = [], action) {
    switch (action.type) {
    case GET_TRANSFERS:
        if (action.payload.hasOwnProperty('data')
         && action.payload.data.hasOwnProperty('transfers')) {
            return updates(state, action.payload.data.transfers);
        }
        break;
    case UPDATE_TRANSFER: {
        return update(state, action.payload.data.transfers[0]);
    }
    case DELETE_TRANSFER:
        if (action.payload.hasOwnProperty('data')
         && action.payload.data.hasOwnProperty('transfer')) {
            return deleteItem(state, action.payload.data.transfer.id);
        }
        break;
    case GET_TRANSFERS_ERROR:
    case UPDATE_TRANSFERS_ERROR:
        console.log('Error updating');
        return state;
    default:
      // Nothing
    }
    return state;
}
