export const UPDATE_TRANSFER = 'UPDATE_TRANSFER';
export const DELETE_TRANSFER = 'DELETE_TRANSFER';

export function updateTransfer(transfer) {
  return {
    type: UPDATE_TRANSFER,
    payload: transfer,
  };
}

export function deleteTransfer(transfer) {
  return {
    type: DELETE_TRANSFER,
    payload: transfer,
  };
}
