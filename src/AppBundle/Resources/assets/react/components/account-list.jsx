import React, { Component } from 'react';
import AccountCard from './account-card';


var accounts = opg.accounts;

class AccountList extends Component {

    constructor (props) {
        super(props);
        this.selectAccount = this.selectAccount.bind(this);
    }
    
    selectAccount (account) {
        if (this.props.selectAccount) {
            this.props.selectAccount(account);
        }
    }
    
    render () {

        var self = this;
        
        var accountNodes = accounts.map(function(account, i) {
            return (
                <AccountCard account={account} key={i} selectAccount={self.selectAccount} />
            );
        });
        
        return (
            <ul className="card-list">
                {accountNodes}
            </ul>
        );
    }
}

export default AccountList;
