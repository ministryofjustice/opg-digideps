export const SET_ACTIVE_TRANSFER = 'SET_ACTIVE_TRANSFER';
export const CLEAR_ACTIVE_TRANSFER = 'CLEAR_ACTIVE_TRANSFER';

export function setActiveTransfer(transfer) {
    return {
        type: SET_ACTIVE_TRANSFER,
        payload: transfer,
    };
}

export function clearActiveTransfer() {
    return {
        type: CLEAR_ACTIVE_TRANSFER,
        payload: null,
    };
}
