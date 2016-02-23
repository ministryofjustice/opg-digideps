import React, { Component } from 'react';
import { connect } from 'react-redux';

import AccountCard from '../components/account-card';

class AccountList extends Component {

  accountListItem = (account, i) => {
    return (
      <li className="card-item" key={i}>
        <AccountCard account={account} selectAccount={this.props.selectAccount} />
      </li>
    );
  }

  orderedAccounts(accounts, selectedAccount) {
    let displayAccounts = [];

    if (selectedAccount) {
      displayAccounts.push(selectedAccount);
      const length = accounts.length;

      for (let pos = 0; pos < length; pos += 1) {
        const account = accounts[pos];
        if (account.id !== selectedAccount.id) {
          displayAccounts.push(account);
        }
      }
    } else {
      displayAccounts = accounts;
    }

    return displayAccounts;
  }

  render() {
    const displayAccounts = this.orderedAccounts(this.props.accounts, this.props.selectedAccount);

    return (
      <ul className="card-list">
        {displayAccounts.map(this.accountListItem)}
      </ul>
    );
  }
}

AccountList.propTypes = {
  selectedAccount: React.PropTypes.object,
  accounts: React.PropTypes.array,
  selectAccount: React.PropTypes.func,
};

function mapStateToProps({ accounts }) {
  return { accounts };
}

export default connect(mapStateToProps)(AccountList);
