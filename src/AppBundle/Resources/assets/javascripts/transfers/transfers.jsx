var React = require('react');
var ReactDOM = require('react-dom');
var MoneyTransfers = require('./money-transfers');

var transfers = [
    {
        "id": 0,
        "amount": 0,
        "accountFrom": null,
        "accountTo": null
    }
];

ReactDOM.render(
    <MoneyTransfers transfers={transfers} />,
    document.getElementById('transfers')
);
