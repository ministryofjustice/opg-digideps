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
    }

    update (key,value) {
        var transfer = {
            id: this.props.transfer.id,
            accountFrom: this.props.transfer.accountFrom,
            accountTo: this.props.transfer.accountTo,
            amount: this.props.transfer.amount
        };

        transfer[key] = value;
        console.log('trigger update');
        $(document).trigger("updateTransfer", [transfer]);

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
        
        return (
            <li className="transfer grid-row">
                <div className="column-one-third">
                    <div className="form-group">
                        <label className="form-label" htmlFor="balance">Amount:</label>
                        <span className="input-group-prefix">£</span>
                        <input type="text" 
                               id="balance" 
                               name="account[balance]" 
                               className="form-control form-control__number" 
                               value={transfer.amount}
                               onChange={this.setAmount}/>
                    </div>
                </div>
                <div className="column-one-third">
                    <div className="form-label">Transferred from:</div>
                    <TransferAccount account={transfer.accountFrom} selectAccount={this.setAccountFrom} />
                </div>
                <div className="column-one-third">
                    <div className="form-label">Transferred to:</div>
                    <TransferAccount account={transfer.accountTo} selectAccount={this.setAccountTo}/>
                </div>
            </li>
        );
    }

}

export default MoneyTransfer;
