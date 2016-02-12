var React = require('react');
var AccountCard = require('./account-card');
var AccountList = require('./account-list');


module.exports = React.createClass({
    getInitialState: function () {
        return {
            open: false,
            account: null
        }
    },
    clickEdit: function () {
        this.setState({open:true});
    },
    selectAccount: function (account) {
        this.setState({
            open: false,
            account: account
        });
        
        if (this.props.selectAccount) {
            this.props.selectAccount(account);
        }
    },
    render: function () {
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
});
