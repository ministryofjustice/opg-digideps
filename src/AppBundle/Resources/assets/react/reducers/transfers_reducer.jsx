import { UPDATE_TRANSFER, DELETE_TRANSFER } from '../actions';

const defaultState = [
  {
    id: 0,
    amount: null,
    accountFrom: null,
    accountTo: null,
  },
];

function updateTransfer(state, transfer) {
  return state.map(item => {
    if (item.id === transfer.id) {
      return transfer;
    }

    return item;
  });
}

function deleteTransfer(state, transfer) {
  return state.filter(item => item.id === transfer.id);
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


/*
checkToAddNew() {
  let complete = true;
  const transfers = this.state.transfers;
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
      id: transfers.length,
      accountFrom: null,
      accountTo: null,
      amount: null,
    });

    this.setState({ transfers });
    this.fakeSave();
  } else {
    $('#page-section-title-container').find('.info').text('');
  }
}

  fakeSave() {
    const statusElement = $('#page-section-title-container').find('.info');
    statusElement.html('<span id="save-status" data-status="saving">Saving...</span>');
    window.setTimeout(() => {
      statusElement.html('<span id="save-status" data-status="saved">Saved</span>');
    }, 1000);
  }
*/
