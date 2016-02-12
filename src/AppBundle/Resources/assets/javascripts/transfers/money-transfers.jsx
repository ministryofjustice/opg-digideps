var React = require('react');
var MoneyTransfer = require('./money-transfer');


module.exports = React.createClass({

    render: function () {

        var transferNodes = this.props.transfers.map(function(transfer, i) {
            return (
                <MoneyTransfer {...transfer} key={i} />
            );
        });
        return (
            <ul id="transfers" className="card-list">
                {transferNodes}
            </ul>
        );
    }
});
