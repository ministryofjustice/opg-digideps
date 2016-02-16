import React, { Component } from 'react';

import AccountCard from './account-card';
import AccountList from './account-list';

class TransferAccount extends Component {
    
    constructor (props) {
        super(props);
        this.state = {
            open: false,
            account: null
        }

        this.clickEdit = this.clickEdit.bind(this);
        this.selectAccount = this.selectAccount.bind(this);
    }
    
    clickEdit () {
        this.setState({open:true});
    }
    
    selectAccount(account) {
        this.setState({
            open: false,
            account: account
        });
        
        if (this.props.selectAccount) {
            this.props.selectAccount(account);
        }
    }
    
    render () {
        return (
            <div>
                {this.state.open == true && (
                    <AccountList selectAccount={this.selectAccount} />
                )}

                {this.state.open == false && this.state.account !== null && (
                    <AccountCard account={this.state.account} selectAccount={this.clickEdit} />
                )}

                {this.state.open == false && this.state.account === null && (
                    <a className="card add" onClick={this.clickEdit}>Select account</a>
                )}
            </div>
        );
    }
}

export default TransferAccount;
