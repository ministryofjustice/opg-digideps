/* global opg: true */
import { NO_TRANSFERS } from '../actions/report_actions';
import { GET_TRANSFERS } from '../actions/transfers_actions';

import update from 'react-addons-update';

export default function(state = opg.report, action) {
    switch (action.type){
    case GET_TRANSFERS:
        if (action.payload.hasOwnProperty('data')
         && action.payload.data.hasOwnProperty('noTransfers')) {
            return update(state, {
                noTransfers: { $set: action.payload.data.noTransfers }
            });
        }
        break;
    case NO_TRANSFERS:
        return update(state, {
            noTransfers: { $set: action.payload }
        });
    }

    return state;
}
