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

export const SAVED_TRANSFER = 'SAVED_TRANSFER';


export function getTransfers(reportId) {
    const url = `/report/${reportId}/transfers`;
    return {
        types: [GET_TRANSFERS, GET_TRANSFERS_ERROR],
        promise: axios.get(url),
        reportId
    };
}

function save(transfer) {
    const url = `/report/${transfer.reportId}/transfers/${transfer.id}`;
    const request = axios.put(url, { transfer });
    return {
        types: [SAVE_TRANSFER, SAVED_TRANSFER, SAVE_TRANSFER_ERROR],
        promise: request
    };
}

function add(transfer) {
    const url = `/report/${transfer.reportId}/transfers`;
    const request = axios.post(url, { transfer });
    return {
        types: [ADD_TRANSFER, SAVED_TRANSFER, SAVE_TRANSFER_ERROR],
        promise: request
    };
}

function update(transfer) {
    return {
        type: UPDATE_TRANSFER,
        payload: transfer,
    };
}


// Pass all changes straight through vi an update, and then decide
// if we also need to save them to the server.
// no id incomplete				    update
// no id complete waiting			update
// id incomplete					update
// no id complete not waiting		mark waiting, update, post
// id complete						update put
export function updateTransfer(transfer) {
    // go back to using thunk so I can dispatch multiple actions
    return (dispatch) => {

        if (!completeTransfer(transfer) ||
            completeTransfer(transfer) && transfer.id === null && transfer.waitingForId)
        {
            dispatch(update(transfer));
            return;
        }

        if (transfer.id === null && !transfer.waitingForId) {
            transfer.waitingForId = true;
            dispatch(update(transfer));
            dispatch(add(transfer));
        }

        if (transfer.id !== null && completeTransfer(transfer)) {
            dispatch(update(transfer));
            dispatch(save(transfer));
        }

    };
}

// Mark locally that the transfer is deleted and save that to the server.
// Todo - what happens if a save fails? Should we put it back or just report
// an error?
export function deleteTransfer(transfer) {
    return (dispatch) => {
        const url = `/report/${transfer.reportId}/transfers/${transfer.id}`;
        const request = axios.delete(url);

        dispatch({
            types: [SAVE_DELETE_TRANSFER, SAVE_TRANSFER_ERROR],
            promise: request
        });

        dispatch({
            type: DELETE_TRANSFER,
            payload: transfer,
        });
    };
}
