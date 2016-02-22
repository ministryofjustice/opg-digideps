/* global opg: true */

import React from 'react';
import AccountCard from './account-card';

const AccountList = ({ selectAccount, selectedAccount }) => {
  let accounts;

  if (selectedAccount) {
    accounts = [];
    accounts.push(selectedAccount);

    let pos = 0;
    const length = opg.accounts.length;

    for (; pos < length; pos += 1) {
      const account = opg.accounts[pos];
      if (account.id !== selectedAccount.id) {
        accounts.push(account);
      }
    }
  } else {
    accounts = opg.accounts;
  }

  const accountNodes = accounts.map(function (account, i) {
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
  );
};

AccountList.propTypes = {
  selectAccount: React.PropTypes.function,
  selectedAccount: React.PropTypes.object,

};


export default AccountList;
