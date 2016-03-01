import { GET_TRANSFERS, CREATE_TRANSFER, UPDATE_TRANSFER, DELETE_TRANSFER } from '../actions/transfers_actions';

export default function(state = [], action) {
    switch (action.type) {
    case GET_TRANSFERS:
    case CREATE_TRANSFER:
    case UPDATE_TRANSFER:
    case DELETE_TRANSFER:
        return [{
            reportId: 1,
            id: 999999,
            accountFrom: null,
            accountTo: null,
            amount: null
        }, ...action.payload.data];

    default:
      // Nothing
    }
    return state;
}
