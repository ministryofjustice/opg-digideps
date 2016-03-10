import { SAVE_TRANSFER, ADD_TRANSFER, SAVED_TRANSFER, SAVE_ERROR } from '../actions/transfers_actions';


export default function(state = '', action) {
    switch (action.type) {
    case SAVE_TRANSFER:
    case ADD_TRANSFER:
        return SAVE_TRANSFER;
    case SAVED_TRANSFER:
        return SAVED_TRANSFER;
    case SAVE_ERROR:
        return SAVE_ERROR;
    default:
      // Nothing
    }
    return state;
}
