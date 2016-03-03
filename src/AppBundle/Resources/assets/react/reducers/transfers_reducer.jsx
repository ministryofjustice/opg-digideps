import {
    GET_TRANSFERS,
    UPDATE_TRANSFER,
    DELETE_TRANSFER,
    GET_TRANSFERS_ERROR,
    UPDATE_TRANSFERS_ERROR
  } from '../actions/transfers_actions';

function update(state, transfer) {
    // If this is an update to a new transfer that is not yet saved
    const clonedState = state.slice(0);
    const newState = clonedState.map(item => {
        if (item.id === transfer.id ){
            return transfer;
        }
        return item;
    });

    return newState;
}
function updates(state, transfers) {
    return [{
        reportId: 1,
        id: null,
        accountFrom: null,
        accountTo: null,
        amount: null
    }, ...transfers];
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
