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
export const ADDED_TRANSFER = 'ADDED_TRANSFER';
export const SAVE_DELETE_TRANSFER = 'SAVE_DELETE_TRANSFER';
export const SAVED_TRANSFER = 'SAVED_TRANSFER';

export const GET_TRANSFERS_ERROR = 'GET_TRANSFERS_ERROR';
export const SAVE_TRANSFER_ERROR = 'SAVE_TRANSFER_ERROR';

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
export function updateTransfer(transfer) {
    return {
        type: UPDATE_TRANSFER,
        payload: transfer,
    };
}
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

export function saveTransfer(transfer) {
    // Strip , out of currency
    let transferClone = Object.assign({}, transfer);
    transferClone.amount = parseFloat(transferClone.amount.replace(',', ''));

    if (completeTransfer(transfer)) {
        if (transfer.id === null) {
            const request = axios.post(`/report/${transfer.reportId}/transfers`, { transfer: transferClone });
            return {
                types: [SAVE_TRANSFER, ADDED_TRANSFER, SAVE_TRANSFER_ERROR],
                promise: request
            };
        }

        const request = axios.put(`/report/${transfer.reportId}/transfers/${transfer.id}`, { transfer: transferClone });
        return {
            types: [SAVE_TRANSFER, SAVED_TRANSFER, SAVE_TRANSFER_ERROR],
            promise: request
        };
    }
}
