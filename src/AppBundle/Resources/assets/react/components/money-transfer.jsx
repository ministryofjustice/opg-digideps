import React, { Component } from 'react';
import $ from 'jquery';

import TransferAccount from './transfer-account';

class MoneyTransfer extends Component {

    constructor (props) {
        super(props);
        this.update = this.update.bind(this);
        this.setAccountFrom = this.setAccountFrom.bind(this);
        this.setAccountTo = this.setAccountTo.bind(this);
        this.setAmount = this.setAmount.bind(this);
        this.deleteTransfer = this.deleteTransfer.bind(this);
    }

    update (key,value) {
        var transfer = {
            id: this.props.transfer.id,
            accountFrom: this.props.transfer.accountFrom,
            accountTo: this.props.transfer.accountTo,
            amount: this.props.transfer.amount
        };

        transfer[key] = value;
        $(document).trigger("updateTransfer", [transfer]);

    }
    
    deleteTransfer () {

        var transfer = {
            id: this.props.transfer.id,
            accountFrom: this.props.transfer.accountFrom,
            accountTo: this.props.transfer.accountTo,
            amount: this.props.transfer.amount
        };

        $(document).trigger("deleteTransfer", [transfer]);
        
    }
    
    setAccountFrom (account) {
        this.update('accountFrom', account);
    }
    
    setAccountTo (account) {
        this.update('accountTo', account);
    }
    
    setAmount (event) {
        this.update("amount", event.target.value);
    }
    
    render () {

        var transfer = this.props.transfer;
        var completed = true;

        if (transfer.amount == null || transfer.amount == "" || transfer.amount == "0" || !transfer.accountFrom || !transfer.accountTo) {
            completed = false;
        }
        
        return (
            <li className="transfer grid-row">
                
                <div className="column-one-third">
                    <div className="form-label">From:</div>
                    <TransferAccount account={transfer.accountFrom} selectAccount={this.setAccountFrom} />
                    {completed && (
                        <a className="button button-warning delete-button" onClick={this.deleteTransfer}>Delete</a>
                    )}
                </div>
                <div className="column-one-third">
                    <div className="form-label">To:</div>
                    <TransferAccount account={transfer.accountTo} selectAccount={this.setAccountTo}/>
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
                               onChange={this.setAmount}/><br/>
                        
                    </div>
                </div>
            </li>
        );
    }

}

export default MoneyTransfer;
