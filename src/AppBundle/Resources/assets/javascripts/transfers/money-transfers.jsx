var React = require('react');
var MoneyTransfer = require('./money-transfer');


module.exports = React.createClass({

    getInitialState: function () {
        return {
            transfers: this.props.transfers
        }
    },
    componentDidMount: function () {
        updateTransfer = this.updateTransfer;
        $(document).on("updateTransfer", function (event, transfer) {
            updateTransfer(transfer);
        });  
    },
    updateTransfer: function (transfer) {
        var transfers = this.state.transfers,
            pos = transfers.length;
        
        for (; pos > 0; pos -= 1) {
            if (transfers[pos -1].id === transfer.id) {
                transfers[pos -1] = transfer;
            }
        }
        
        this.setState({transfers:transfers});
    
        // Check to see if we need to add a new one?
        this.checkToAddNew();
    
    },
    checkToAddNew: function () {
      
        var complete = true;
        var transfers = this.state.transfers;
        var pos = transfers.length;
        var transfer;
        
        // Scan through all the things.
        for (; pos != 0; pos -= 1) {
            transfer = transfers[pos - 1];
            
            if (transfer.amount == "0" || !transfer.accountFrom || !transfer.accountTo) {
                complete = false;
            }
        
        }
        
        if (complete === true) {
            transfers.push({
                id: transfers.length,
                accountFrom: null,
                accountTo: null,
                amount: "0"
            });
            
            this.setState({transfers: transfers});
            
        }
        
    },
    render: function () {

        var transfers = this.state.transfers;
        
        var transferNodes = Object.keys(transfers).map(function(key) {
            var transfer = transfers[key];
            return (
                <MoneyTransfer {...transfer} key={transfer.id} />
            );
        });
        return (
            <ul id="transfers" className="card-list">
                {transferNodes}
            </ul>
        );
    }
});
