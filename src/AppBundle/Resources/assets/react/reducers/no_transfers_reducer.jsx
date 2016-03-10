import { NO_TRANSFERS } from '../actions/transfers_actions';

export default function(state = false, action) {
    switch (action.type){
    case NO_TRANSFERS:
        return action.payload;
    }

    return state;
}
