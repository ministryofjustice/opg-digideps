import axios from 'axios';

export const GET_TRANSFERS = 'GET_TRANSFERS';
export const CREATE_TRANSFER = 'CREATE_TRANSFER';
export const UPDATE_TRANSFER = 'UPDATE_TRANSFER';
export const DELETE_TRANSFER = 'DELETE_TRANSFER';

/*
 * GET  	/report/{reportId}/transfers/edit			html
 * GET  	/report/{reportId}/transfers
 * POST 	/report/{reportId}/transfers
 * PUT  	/report/{reportId}/transfers/{transferId}
 * DELETE	/report/{reportId}/transfers/{transferId}
*/

export function getTransfers(reportId) {
    const url = `/report/${reportId}/transfers`;
    const request = axios.get(url);

    return {
        type: GET_TRANSFERS,
        payload: request
    };
}

export function createTransfer(transfer) {

    const url = `/report/${transfer.reportId}/transfers`;
    const request = axios.post(url, transfer);

    return {
        type: UPDATE_TRANSFER,
        payload: request,
    };
}

export function updateTransfer(transfer) {
    const url = `/report/${transfer.reportId}/transfers`;
    const request = axios.put(url, transfer);

    return {
        type: UPDATE_TRANSFER,
        payload: request,
    };
}

export function deleteTransfer(transfer) {

    const url = `/report/{transfer.reportId}/transfers/${transfer.id}`;
    const request = axios.delete(url);

    return {
        type: UPDATE_TRANSFER,
        payload: request,
    };

}
