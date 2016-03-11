import { SAVE_TRANSFER, ADDED_TRANSFER, SAVED_TRANSFER, SAVE_ERROR } from '../actions/transfers_actions';
import { SAVE_NO_TRANSFERS, SAVED_NO_TRANSFERS, SAVE_NO_TRANSFERS_ERROR } from '../actions/report_actions';

export default function(state = 'NOTHING', action) {
    switch (action.type) {
    case SAVE_TRANSFER:
    case SAVE_NO_TRANSFERS:
        return SAVE_TRANSFER;
    case SAVED_TRANSFER:
    case SAVED_NO_TRANSFERS:
        return SAVED_TRANSFER;
    case ADDED_TRANSFER:
        return SAVED_TRANSFER;
    case SAVE_ERROR:
    case SAVE_NO_TRANSFERS_ERROR:
        return SAVE_ERROR;
    default:
      // Nothing
    }
    return state;
}
