import React, { Component } from 'react';

import AccountCard from './account-card';
import AccountList from '../containers/account-list';

class TransferAccount extends Component {

  constructor(props) {
    super(props);
    this.state = {
      open: false
    };
  }

  clickEdit = () => {
    this.setState({
      open: true,
      selectedAccount: this.state.selectedAccount,
    });
  }

  selectAccount = (account) => {
    this.setState({
      open: false,
      account,
    });

    this.props.selectAccount(account);
  }

  render() {
    if (this.state.open) {
      return (
        <AccountList
          selectedAccount={this.state.account}
          selectAccount={this.selectAccount}
        />
      );
    } else if (!this.state.open && this.state.account) {
      return (
        <AccountCard account={this.state.account} />
      );
    }

    return (
      <a className="card add" onClick={this.clickEdit}>Select account</a>
    );
  }
}

TransferAccount.propTypes = {
  account: React.PropTypes.object,
  selectAccount: React.PropTypes.func,
};

export default TransferAccount;
