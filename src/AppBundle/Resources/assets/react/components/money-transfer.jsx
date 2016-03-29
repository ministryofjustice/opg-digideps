import React, { Component } from 'react';
import TransferAccount from './transfer-account';
import { completeTransfer, formatCurrency } from '../utils/transfer_utils';

export default class MoneyTransfer extends Component {

    constructor(props) {
        super(props);
        this.state = {
            deleteConfirm: false,
            waitingToSave: false,
        };
    }

    componentWillUnmount() {
        clearTimeout(this.timer);
    }

    setAccountFrom = (account) => {
        const newTransferState = this.mutate('accountFrom', account);
        this.props.updateTransfer(newTransferState);

        if (completeTransfer(newTransferState)) {
            this.props.saveTransfer(newTransferState);
        }
    }

    setAccountTo = (account) => {
        const newTransferState = this.mutate('accountTo', account);
        this.props.updateTransfer(newTransferState);

        if (completeTransfer(newTransferState)) {
            this.props.saveTransfer(newTransferState);
        }
    }

    amountChange = (event) => {

        const newTransferState = this.mutate('amount', event.target.value);
        let waitingToSave = false;

        this.setState({
            deleteConfirm: false,
            waitingToSave,
        });

        this.props.updateTransfer(newTransferState);

        if (completeTransfer(newTransferState)) {
            clearTimeout(this.timer);
            this.timer = setTimeout(() => {
                this.save();
            }, 3000);
        }
    }

    save = () => {
        clearTimeout(this.timer);
        if (completeTransfer(this.props.transfer) && !this.props.transfer.error) {
            this.props.saveTransfer(this.props.transfer);
        }
    }

    amountBlur = (event) => {
        clearTimeout(this.timer);

        let value = formatCurrency(event.target.value);
        let valueCopy = value.replace(/^\s+|\s+$/g, '');
        valueCopy = valueCopy.replace(',', '');
        const newTransferState = this.mutate('amount', valueCopy);

        this.props.updateTransfer(newTransferState);
        this.save();
    }

    clickDelete = () => {
        clearTimeout(this.timer);
        this.setState({
            waitingToSave: false,
            deleteConfirm: true,
        });
    }

    cancelDelete = () => {
        this.setState({
            waitingToSave: false,
            deleteConfirm: false,
        });
    }

    clickDeleteConfirm = () => {
        this.props.deleteTransfer(this.props.transfer);
        this.setState({
            waitingToSave: false,
            deleteConfirm: false,
        });
    }

    setActiveTransfer = () => {
        this.props.setActiveTransfer(this.props.transfer);
    }

    mutate(key, value) {
        const newTransferState = Object.assign(this.props.transfer);
        newTransferState[key] = value;
        return newTransferState;
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

        let className = 'transfer';

        if (this.props.activeTransfer !== null) {
            if (this.props.activeTransfer.id !== this.props.transfer.id) {
                className += ' inactive';
            } else {
                className += ' active';
            }
        }

        if (this.props.transfer.error) {
            className += ' error';
        }

        return (
            <li className={className}>
                <div className="grid-row card-select-row">
                    <div className="column-one-half">
                        <div className="form-label">From:</div>
                        <TransferAccount
                            account={transfer.accountFrom}
                            selectAccount={this.setAccountFrom}
                            setActiveTransfer={this.setActiveTransfer}
                            clearActiveTransfer={this.props.clearActiveTransfer}
                        />
                    </div>
                    <div className="column-one-half">
                        <div className="form-label">To:</div>
                        <TransferAccount
                            account={transfer.accountTo}
                            selectAccount={this.setAccountTo}
                            setActiveTransfer={this.setActiveTransfer}
                            clearActiveTransfer={this.props.clearActiveTransfer}
                         />
                    </div>
                </div>
                <div className="grid-row">
                    <div className="column-one-half">
                        <div className="form-group">
                            <label className="form-label" htmlFor="balance">Amount:</label>
                            {(this.props.transfer.error) && (
                                <span className="error-message">{this.props.transfer.error}</span>
                            )}
                            <span className="input-group-prefix">£</span>
                            <input type="text"
                              id="balance"
                              name="account[balance]"
                              className="form-control form-control__number"
                              value={transfer.amount}
                              onChange={this.amountChange}
                              onBlur={this.amountBlur}
                            />
                        </div>
                    </div>
                    <div className="column-one-half">
                        { (completed && transfer.id && this.state.deleteConfirm === false) && (
                            <a className="button button-warning delete-button" onClick={this.clickDelete}>
                                Delete
                            </a>
                        )}
                        { (completed && transfer.id && this.state.deleteConfirm === true) && (
                            <div className="confirm-bar button-bar">
                                <p className="delete-confirm-message">
                                    Are you sure that you want to delete this decision?
                                </p>
                                <a className="button-warning behat-link-delete-confirm"
                                    onClick={this.clickDeleteConfirm}>
                                    Delete
                                </a>
                                <a className="button-link cancel-link"
                                    onClick={this.cancelDelete}>Cancel
                                </a>
                            </div>
                        )}
                    </div>
                </div>
            </li>
        );
    }
}

MoneyTransfer.propTypes = {
    account: React.PropTypes.object,
    transfer: React.PropTypes.object,
    updateTransfer: React.PropTypes.func,
    deleteTransfer: React.PropTypes.func,
    saveTransfer: React.PropTypes.func,
    setActiveTransfer: React.PropTypes.func,
    clearActiveTransfer: React.PropTypes.func,
    activeTransfer: React.PropTypes.object,
};
