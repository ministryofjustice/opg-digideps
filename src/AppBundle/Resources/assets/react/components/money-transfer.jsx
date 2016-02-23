import React, { Component } from 'react';

import { updateTransfer, deleteTransfer } from '../actions';
import TransferAccount from './transfer-account';

export default class MoneyTransfer extends Component {
  setAccountFrom = (account) => {
    this.update('accountFrom', account);
  }

  setAccountTo = (account) => {
    this.update('accountTo', account);
  }

  amountChange = (event) => {
    this.update('amount', event.target.value);
  }

  clickDelete = () => {
    deleteTransfer(this.props.transfer);
  }

  update(key, value) {
    const transfer = {
      id: this.props.transfer.id,
      accountFrom: this.props.transfer.accountFrom,
      accountTo: this.props.transfer.accountTo,
      amount: this.props.transfer.amount,
    };

    transfer[key] = value;

    updateTransfer(transfer);
  }

  render() {
    const transfer = this.props.transfer;
    let completed = true;

    if (transfer.amount === null ||
        transfer.amount === '' ||
        transfer.amount === '0' ||
        !transfer.accountFrom || !transfer.accountTo) {
      completed = false;
    }

    return (
      <li className="transfer grid-row">
        <div className="column-one-third">
          <div className="form-label">From:</div>
          <TransferAccount account={transfer.accountFrom} selectAccout={this.setAccountFrom} />
          {completed && (
            <a className="button button-warning delete-button" onClick={this.clickDelete}>
              Delete
            </a>
          )}
        </div>
        <div className="column-one-third">
          <div className="form-label">To:</div>
          <TransferAccount account={transfer.accountTo} selectAccount={this.setAccountTo} />
        </div>
        <div className="column-one-third">
          <div className="form-group">
            <label className="form-label" htmlFor="balance">Amount:</label>
            <span className="input-group-prefix">£</span>
            <input type="text"
              id="balance"
              name="account[balance]"
              className="form-control form-control__number"
              value={transfer.amount}
              onChange={this.amountChange}
            />
            <br />
          </div>
        </div>
      </li>
    );
  }
}

MoneyTransfer.propTypes = {
  account: React.PropTypes.object,
  transfer: React.PropTypes.object,
};
