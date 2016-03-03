import axios from 'axios';

export const GET_TRANSFERS = 'GET_TRANSFERS';
export const CREATE_TRANSFER = 'CREATE_TRANSFER';
export const UPDATE_TRANSFER = 'UPDATE_TRANSFER';
export const DELETE_TRANSFER = 'DELETE_TRANSFER';
export const SAVE_STARTED = 'SAVE_STARTED';
export const GET_TRANSFERS_ERROR = 'GET_TRANSFERS_ERROR';
export const UPDATE_TRANSFERS_ERROR = 'UPDATE_TRANSFERS_ERROR';

/*
 * GET  	/report/{reportId}/transfers/edit			html
 * GET  	/report/{reportId}/transfers
 * POST 	/report/{reportId}/transfers
 * PUT  	/report/{reportId}/transfers/{transferId}
 * DELETE	/report/{reportId}/transfers/{transferId}
*/

export function getTransfers(reportId) {
    const url = `/report/${reportId}/transfers`;
    return {
        types: [GET_TRANSFERS, GET_TRANSFERS_ERROR],
        promise: axios.get(url),
        reportId
    };
}

export function createTransfer(transfer) {
    return (dispatch) => {

        if (transfer.accountFrom === null ||
            transfer.accountTo === null ||
            transfer.amount === 0 ||
            transfer.amount === null) {

            return;

        }

        const url = `/report/${transfer.reportId}/transfers`;
        const request = axios.post(url, transfer);

        dispatch({
            type: UPDATE_TRANSFER,
            payload: request,
        });
    };
}

export function updateTransfer(transfer) {
    return (dispatch) => {

        if (transfer.accountFrom === null ||
            transfer.accountTo === null ||
            transfer.amount === 0 ||
            transfer.amount === null) {

            return;

        }

        const url = `/report/${transfer.reportId}/transfers`;
        const request = axios.put(url, transfer);

        dispatch({
            type: UPDATE_TRANSFER,
            payload: request,
        });
    };
}

export function deleteTransfer(transfer) {

    const url = `/report/{transfer.reportId}/transfers/${transfer.id}`;
    const request = axios.delete(url);

    return {
        type: [SAVE_STARTED, UPDATE_TRANSFER, UPDATE_TRANSFER],
        payload: request,
    };

}
