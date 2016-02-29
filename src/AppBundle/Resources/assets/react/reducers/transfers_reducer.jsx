import { UPDATE_TRANSFER, DELETE_TRANSFER } from '../actions';
import $ from 'jquery';


const defaultState = [
  {
    id: Math.floor((Math.random() * 100) + 1),
    amount: null,
    accountFrom: null,
    accountTo: null,
  },
];

function checkToAddNew(transfers) {
  let complete = true;

  let pos = transfers.length;

  // Scan through all the things.
  for (; pos !== 0; pos -= 1) {
    const transfer = transfers[pos - 1];

    if (transfer.amount === null ||
      transfer.amount === '' ||
      transfer.amount === '0' ||
      !transfer.accountFrom ||
      !transfer.accountTo) {
      complete = false;
    }
  }

  if (complete === true) {
    transfers.push({
      id: Math.floor((Math.random() * 100) + 1),
      accountFrom: null,
      accountTo: null,
      amount: null,
    });
  } else {
    $('#page-section-title-container').find('.info').text('');
  }
}

function fakeSave() {
  const statusElement = $('#page-section-title-container').find('.info');
  statusElement.html('<span id="save-status" data-status="saving">Saving...</span>');
  window.setTimeout(() => {
    statusElement.html('<span id="save-status" data-status="saved">Saved</span>');
  }, 1000);
}

function updateTransfer(state, transfer) {
  fakeSave();
  const newState = state.map(item => {
    if (item.id === transfer.id) {
      return transfer;
    }

    return item;
  });
  checkToAddNew(newState);
  return newState;
}

function deleteTransfer(state, transfer) {
  return state.filter(item => item.id !== transfer.id);
}

export default function (state = defaultState, action) {
  switch (action.type) {
    case UPDATE_TRANSFER:
      return updateTransfer(state, action.payload);
    case DELETE_TRANSFER:
      return deleteTransfer(state, action.payload);
    default:
      // Nothing
  }
  return state;
}
