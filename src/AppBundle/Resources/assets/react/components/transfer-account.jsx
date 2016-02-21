/*jshint esversion: 6 */
/*jshint browser: true */

import React, { Component } from 'react';
import AccountCard from './account-card';
import AccountList from './account-list';

class TransferAccount extends Component {

    constructor (props) {
        super(props);
        this.state = {
            open: false,
            account: props.account
        };
    }

    clickEdit = () => {
        this.setState({open:true});
    }

    selectAccount = (account) => {
        this.setState({
            open: false,
            account: account
        });

        if (this.props.selectAccount) {
            this.props.selectAccount(account);
        }
    }

    render () {

        if (this.state.open) {
            return (<AccountList selectAccount={this.selectAccount} selectedAccount={this.state.account}/>);
        } else if (!this.state.open && this.state.account) {
            return (<AccountCard account={this.state.account} selectAccount={this.clickEdit} />);
        } else {
            return (<a className="card add" onClick={this.clickEdit}>Select account</a>);
        }

    }
}

export default TransferAccount;
