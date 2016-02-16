import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import MoneyTransfers from './components/money-transfers';

var transfers = [
    {
        "id": 0,
        "amount": null,
        "accountFrom": null,
        "accountTo": null
    }
];

ReactDOM.render(<MoneyTransfers transfers={transfers} />, document.querySelector('#transfers'));
