import { SET_ACTIVE_TRANSFER, CLEAR_ACTIVE_TRANSFER } from '../actions/active_transfer_actions';


export default function(state = null, action) {
    switch (action.type){
    case SET_ACTIVE_TRANSFER:
        return action.payload;
    case CLEAR_ACTIVE_TRANSFER:
        return null;
    default:
        return state;
    }
}
