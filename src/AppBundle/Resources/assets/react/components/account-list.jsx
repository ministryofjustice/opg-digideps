import React, { Component } from 'react';
import AccountCard from './account-card';


var accounts = [
    {
        "accountId": 1,
        "name": "Smile",
        "type": "Current",
        "accountNumber": 1234,
        "sortCode": "112233",
        "openingBalance": 100,
        "closingBalance": 200
    },
    {
        "accountId": 2,
        "name": "Barclays",
        "type": "Current",
        "accountNumber": 5555,
        "sortCode": "333333",
        "openingBalance": 10000,
        "closingBalance": 20000
    },
    {
        "accountId": 3,
        "name": "Natwest",
        "type": "Current",
        "accountNumber": 5511,
        "sortCode": "887744",
        "openingBalance": 1400,
        "closingBalance": 23200
    }
];

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
