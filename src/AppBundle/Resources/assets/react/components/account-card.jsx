import React, { Component } from 'react';

class AccountCard extends Component {

    constructor (props) {
        super(props);
        this.clickCard = this.clickCard.bind(this);
    }
    
    clickCard () {
        if (this.props.selectAccount) {
            this.props.selectAccount(this.props.account);
        }
    }
    
    render () {
        
        var account = this.props.account;
        
        return (
            <div className="card-item">
                <div className="card" onClick={this.clickCard}>
                    <div className="account card-title">{account.name} {account.type}</div>
                    <dl className="labelvalue">
                        <dt className="label">Account number:</dt>
                        <dd className="value">0000{account.accountNumber}</dd>
                        <dt className="label">Sort code:</dt>
                        <dd className="value">{account.sortCode}</dd>
                    </dl>
                </div>
            </div>
        );
    }
}

export default AccountCard;
