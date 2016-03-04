import axios from 'axios';
import { completeTransfer } from '../utils/transfer_utils';

/*
 * GET  	/report/{reportId}/transfers/edit			html
 * GET  	/report/{reportId}/transfers
 * POST 	/report/{reportId}/transfers
 * PUT  	/report/{reportId}/transfers/{transferId}
 * DELETE	/report/{reportId}/transfers/{transferId}
*/

// Network actions
export const GET_TRANSFERS = 'GET_TRANSFERS';
export const SAVE_TRANSFER = 'SAVE_TRANSFER';
export const ADD_TRANSFER = 'ADD_TRANSFER';
export const SAVE_DELETE_TRANSFER = 'SAVE_DELETE_TRANSFER';

export const GET_TRANSFERS_ERROR = 'GET_TRANSFERS_ERROR';
export const SAVE_TRANSFER_ERROR = 'SAVE_TRANSFER_ERROR';
export const ADD_TRANSFER_ERROR = 'SAVE_TRANSFER_ERROR';

export const UPDATE_TRANSFER = 'UPDATE_TRANSFER';
export const DELETE_TRANSFER = 'DELETE_TRANSFER';


export function getTransfers(reportId) {
    const url = `/report/${reportId}/transfers`;
    return {
        types: [GET_TRANSFERS, GET_TRANSFERS_ERROR],
        promise: axios.get(url),
        reportId
    };
}

function save(transfer, request) {
    return {
        types: [SAVE_TRANSFER, SAVE_TRANSFER_ERROR],
        promise: request,
        payload: {
            data: {
                transfers: [transfer]
            }
        },
    };
}

function update(transfer) {
    return {
        type: UPDATE_TRANSFER,
        payload: {
            data: {
                transfers: [transfer]
            }
        }
    };
}
// Pass all changes straight through vi an update, and then decide
// if we also need to save them to the server.
export function updateTransfer(transfer) {
    // go back to using thunk so I can dispatch multiple actions
    return (dispatch) => {

        if (!completeTransfer(transfer)) {
            dispatch(update(transfer));

        } else {

            if (transfer.id === null) {
                transfer.waitingForId = true;
            }

            const request = axios.post(`/report/${transfer.reportId}/transfers`, {transfer});
            dispatch(save(transfer, request));
        }

    };
}

export function deleteTransfer(transfer) {

    const url = `/report/{transfer.reportId}/transfers/${transfer.id}`;
    const request = axios.delete(url);

    return {
        type: DELETE_TRANSFER,
        payload: request,
    };

}
