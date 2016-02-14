var React = require('react');
var ReactDOM = require('react-dom');
var MoneyTransfers = require('./money-transfers');

var transfers = {
    "0": {
        "amount": 0,
        "accountFrom": null,
        "accountTo": null
    };
];
var accounts = [
    {
        "accountId": 1,
        "name": "Smile",
        "type": "Current",
        "accountNumber": 1234,
        "sortCode": "112233",
        "openingBalance": 100,
        "closingBalance": 200
    },
    {
        "accountId": 2,
        "name": "Barclays",
        "type": "Current",
        "accountNumber": 5555,
        "sortCode": "333333",
        "openingBalance": 10000,
        "closingBalance": 20000
    },
    {
        "accountId": 3,
        "name": "Natwest",
        "type": "Current",
        "accountNumber": 5511,
        "sortCode": "887744",
        "openingBalance": 1400,
        "closingBalance": 23200
    }
];

ReactDOM.render(
    <MoneyTransfers transfers={transfers} />,
    document.getElementById('transfers')
);
