var React = require('react');
var TransferAccount = require('./transfer-account');

module.exports = React.createClass({
    getInitialState: function () {
        return {
            accountFrom: this.props.accountFrom,
            accountTo: this.props.accountTo,
            amount: this.props.amount,
            id: this.props.key
        }
    },
    setAccountFrom: function (account) {
        this.setState({accountFrom: account});
    },
    setAccountTo: function (account) {
        this.setState({accountTo: account});
    },
    setAmount: function (amount) {
        this.setState({amount: amount});
    },
    componentWillUpdate: function(nextProps, nextState) {
        if (this.props.updateAccount) {
            this.props.updateAccount(this.state.id, {
                amount: this.state.amount,
                accountFrom: this.state.accountFrom,
                accountTo: this.state.accountTo
            });
        }
    },
    
    
    render: function () {
        return (
            <li className="transfer grid-row">
                <div className="column-one-third">
                    <div className="form-group">
                        <label className="form-label" htmlFor="balance">Amount:</label>
                        <span className="input-group-prefix">£</span>
                        <input type="text" 
                               id="balance" 
                               name="account[balance]" 
                               className="form-control form-control__number" 
                               defaultValue={this.state.amount}/>
                    </div>
                </div>
                <div className="column-one-third">
                    <div className="form-label">Transferred from:</div>
                    <TransferAccount account={this.state.accountFrom} selectAccount={this.setAccountFrom} />
                </div>
                <div className="column-one-third">
                    <div className="form-label">Transferred to:</div>
                    <TransferAccount account={this.state.accountTo} selectAccount={this.setAccountTo}/>
                </div>
            </li>
        );
    }
});
