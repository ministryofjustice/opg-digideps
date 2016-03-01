import React, { Component } from 'react';
import { connect } from 'react-redux';

import AccountCard from '../components/account-card';

class AccountList extends Component {

    accountListItem = (account, i) => (
        <li className="card-item" key={i}>
            <AccountCard account={account} selectAccount={this.props.selectAccount} />
        </li>
    );

    orderedAccounts(accounts, selectedAccount) {
        let displayAccounts;

        if (selectedAccount) {
            const strippedList = accounts.filter(item => item.id !== selectedAccount.id);
            displayAccounts = [selectedAccount, ...strippedList];
        } else {
            displayAccounts = accounts;
        }

        return displayAccounts;
    }

    render() {
        const displayAccounts = this.orderedAccounts(this.props.accounts, this.props.selectedAccount);
        return (
            <ul className="card-list">
                {this.props.selectedAccount ?
                    null :
                    <li className="card-item">
                        <div className="card blank" onClick={this.clickEdit}>&nbsp;</div>
                    </li>
                }
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
