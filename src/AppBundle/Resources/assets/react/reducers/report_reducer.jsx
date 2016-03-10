/* global opg: true */
import { NO_TRANSFERS } from '../actions/report_actions';
import update from 'react-addons-update';

export default function(state = opg.report, action) {
    switch (action.type){
    case NO_TRANSFERS:
        return update(state, {
            noTransfers: { $set: action.payload }
        });
    }

    return state;
}
