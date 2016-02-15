var React = require('react');
var TransferAccount = require('./transfer-account');

module.exports = React.createClass({

    setAccountFrom: function (account) {
        this.update('accountFrom', account);
    },
    setAccountTo: function (account) {
        this.update('accountTo', account);
    },
    setAmount: function (event) {
        this.update("amount", event.target.value);
    },
    update: function(key,value) {
        var transfer = {
            id: this.props.id,
            accountFrom: this.props.accountFrom,
            accountTo: this.props.accountTo,
            amount: this.props.amount
        };
        
        transfer[key] = value;
        $(document).trigger("updateTransfer", [transfer]);
    
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
                               value={this.props.amount}
                               onChange={this.setAmount}/>
                    </div>
                </div>
                <div className="column-one-third">
                    <div className="form-label">Transferred from:</div>
                    <TransferAccount account={this.props.accountFrom} selectAccount={this.setAccountFrom} />
                </div>
                <div className="column-one-third">
                    <div className="form-label">Transferred to:</div>
                    <TransferAccount account={this.props.accountTo} selectAccount={this.setAccountTo}/>
                </div>
            </li>
        );
    }
});
