var React = require('react');
var MoneyTransfer = require('./money-transfer');


module.exports = React.createClass({

    getInitialState: function () {
        return {
            transfers: this.options.transfers
        }
    },
    updateAccount: function (key, transfer) {
        var transfers = this.state.transfers;
        transfers[key] = transfer;
        this.setState({transfers:transfers});
    },
    render: function () {

        var transferNodes = this.props.transfers.map(function(transfer, i) {
            return (
                <MoneyTransfer {...transfer} key={i} updateAccount={this.updateTransfer} />
            );
        });
        return (
            <ul id="transfers" className="card-list">
                {transferNodes}
            </ul>
        );
    }
});
