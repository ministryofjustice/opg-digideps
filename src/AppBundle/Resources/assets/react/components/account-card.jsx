import React, { Component } from 'react';

class AccountCard extends Component {

    selectAccount = (event) => {
        event.stopPropagation();
        event.nativeEvent.stopImmediatePropagation();       // You have to do this to intercept jQuery events
        this.props.selectAccount(this.props.account);
    }

    render() {
        const account = this.props.account;
        return (
            <div className="card" onClick={this.selectAccount}>
                <div className="account card-title">{account.bank} {account.account_type}</div>
                <dl className="labelvalue">
                    <dt className="label">Account number:</dt>
                    <dd className="value">0000{account.account_number}</dd>
                    <dt className="label">Sort code:</dt>
                    <dd className="value">{account.sort_code}</dd>
                </dl>
            </div>
        );
    }
}

AccountCard.propTypes = {
    account: React.PropTypes.object,
    selectAccount: React.PropTypes.func,
};

export default AccountCard;
