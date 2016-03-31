import axios from 'axios';

export const NO_TRANSFERS = 'NO_TRANSFERS';
export const SAVE_NO_TRANSFERS = 'SAVE_NO_TRANSFERS';
export const SAVED_NO_TRANSFERS = 'SAVED_NO_TRANSFERS';
export const SAVE_NO_TRANSFERS_ERROR = 'SAVE_NO_TRANSFERS_ERROR';

export function hasNoTransfers(reportId, noTransfers) {

    return (dispatch) => {

        const url = `/report/${reportId}/notransfers`;
        const request = axios.put(url, { noTransfers });

        dispatch({
            type: NO_TRANSFERS,
            payload: noTransfers,
        });

        dispatch({
            types: [SAVE_NO_TRANSFERS, SAVED_NO_TRANSFERS, SAVE_NO_TRANSFERS_ERROR],
            promise: request
        });

    };


}
