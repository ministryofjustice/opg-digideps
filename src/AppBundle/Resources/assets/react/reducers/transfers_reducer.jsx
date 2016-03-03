import { SAVE_STARTED,
    GET_TRANSFERS,
    CREATE_TRANSFER,
    UPDATE_TRANSFER,
    DELETE_TRANSFER,
    GET_TRANSFERS_ERROR,
    UPDATE_TRANSFERS_ERROR
  } from '../actions/transfers_actions';


export default function(state = [], action) {

    console.log(action.type);

    switch (action.type) {
    case SAVE_STARTED:
        console.log(SAVE_STARTED);
        return state;
    case GET_TRANSFERS_ERROR:
    case UPDATE_TRANSFERS_ERROR:
        console.log('Error updating');
        return state;
    case GET_TRANSFERS:
    case CREATE_TRANSFER:
    case UPDATE_TRANSFER:
    case DELETE_TRANSFER:
        console.log(action.payload);
        if (action.payload.status === 200) {
            return [{
                reportId: 1,
                id: 999999,
                accountFrom: null,
                accountTo: null,
                amount: null
            }, ...action.payload.data];
        }
        break;
    default:
      // Nothing
    }
    return state;
}
