import React, { Component } from 'react';
import MoneyTransfer from './money-transfer';
import $ from 'jquery';

class MoneyTransfers extends Component {

    constructor (props) {
        
        super(props);
        
        this.state = {
            transfers: this.props.transfers
        };

        const updateTransfer = this.updateTransfer.bind(this);
        const deleteTransfer = this.deleteTransfer.bind(this);
        
        $(document).on("updateTransfer", function (event, transfer) {
            updateTransfer(transfer);
        });
        $(document).on("deleteTransfer", function (event, transfer) {
            deleteTransfer(transfer);
        });

    }

    checkToAddNew() {
        var complete = true;
        var transfers = this.state.transfers;
        var pos = transfers.length;
        var transfer;

        // Scan through all the things.
        for (; pos != 0; pos -= 1) {
            transfer = transfers[pos - 1];

            if (transfer.amount == null || transfer.amount == "" || transfer.amount == "0" || !transfer.accountFrom || !transfer.accountTo) {
                complete = false;
            }

        }

        if (complete === true) {
            transfers.push({
                id: transfers.length,
                accountFrom: null,
                accountTo: null,
                amount: null
            });

            this.setState({transfers: transfers});
            this.fakeSave();
        } else {
            $('#page-section-title-container').find('.info').text('');
        }

    }
    
    fakeSave () {
        var statusElement = $('#page-section-title-container').find('.info');
        statusElement.html('<span id="save-status" data-status="saving">Saving...</span>');
        window.setTimeout(function () {
            statusElement.html('<span id="save-status" data-status="saved">Saved</span>');
        }, 1000);
    }
    
    updateTransfer (transfer) {
        
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

    }
    
    deleteTransfer (transfer) {
        var transfers = this.state.transfers,
            pos = transfers.length;

        for (; pos > 0; pos -= 1) {
            if (transfers[pos -1].id === transfer.id) {
                transfers.splice(pos -1]);
            }
        }

        this.setState({transfers:transfers});

        // Check to see if we need to add a new one?
        this.checkToAddNew();
    }
    
    render() {

        var transfers = this.state.transfers;
        
        var transferNodes = Object.keys(transfers).map(function(key) {
            var transfer = transfers[key];
            return (
                <MoneyTransfer transfer={transfer} key={transfer.id} />
            );
        });
        return (
            <ul id="transfers" className="card-list">
                {transferNodes}
            </ul>
        );
    }
}

export default MoneyTransfers;
