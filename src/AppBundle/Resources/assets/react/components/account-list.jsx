/*jshint esversion: 6 */
/*global opg: true */

import React from 'react';
import AccountCard from './account-card';

const AccountList = ({selectAccount}) => {

    var accountNodes = opg.accounts.map(function(account, i) {
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
