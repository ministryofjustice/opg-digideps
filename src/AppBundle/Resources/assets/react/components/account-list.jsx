/*jshint esversion: 6 */
/*global opg: true */

import React from 'react';
import AccountCard from './account-card';

const AccountList = ({selectAccount,selectedAccount}) => {

    var accounts;

    if (selectedAccount) {

        accounts = [];
        accounts.push(selectedAccount);

        var pos = 0,
            length = opg.accounts.length;

        for (; pos < length; pos += 1) {
            let account = opg.accounts[pos];
            if (account.id !== selectedAccount.id) {
                accounts.push(account);
            }
        }

    } else {
        accounts = opg.accounts;
    }

    var accountNodes = accounts.map(function(account, i) {
        return (
            <li className="card-item" key={i}>
                <AccountCard account={account} selectAccount={() => selectAccount(account)} />
            </li>
        );
    });

    return (
        <ul className="card-list">
            {accountNodes}
        </ul>
    )
}

export default AccountList;
