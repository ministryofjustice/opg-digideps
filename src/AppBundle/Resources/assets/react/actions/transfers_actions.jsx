import axios from 'axios';

/*
 * GET  	/report/{reportId}/transfers/edit			html
 * GET  	/report/{reportId}/transfers
 * POST 	/report/{reportId}/transfers
 * PUT  	/report/{reportId}/transfers/{transferId}
 * DELETE	/report/{reportId}/transfers/{transferId}
*/

export const GET_TRANSFERS = 'GET_TRANSFERS';
export const UPDATE_TRANSFER = 'UPDATE_TRANSFER';
export const DELETE_TRANSFER = 'DELETE_TRANSFER';
export const SAVE_TRANSFER = 'SAVE_TRANSFER';
export const GET_TRANSFERS_ERROR = 'GET_TRANSFERS_ERROR';
export const SAVE_TRANSFER_ERROR = 'SAVE_TRANSFER_ERROR';


function completeTransfer(transfer) {
    if (transfer.accountFrom !== null &&
        transfer.accountTo !== null &&
        transfer.amount !== 0 &&
        transfer.amount !== null) {

        return true;

    }

    return false;
}


export function getTransfers(reportId) {
    const url = `/report/${reportId}/transfers`;
    return {
        types: [GET_TRANSFERS, GET_TRANSFERS_ERROR],
        promise: axios.get(url),
        reportId
    };
}

// Pass all changes straight through vi an update, and then decide
// if we also need to save them to the server.
export function updateTransfer(transfer) {

    // if (!completeTransfer(transfer)) {
        return {
            type: UPDATE_TRANSFER,
            payload: {
                data: {
                    transfers: [transfer]
                }
            },
        };
    // }

    const url = `/report/${transfer.reportId}/transfers`;
    let request;

    if (transfer.id !== null) {
        request = axios.post(url, transfer);
    } else {
        request = axios.put(url, transfer);
    }

    return {
        types: [UPDATE_TRANSFER, SAVE_TRANSFER, SAVE_TRANSFER_ERROR],
        payload: request,
        transfer
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
