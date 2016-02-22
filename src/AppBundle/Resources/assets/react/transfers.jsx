/* jshint esversion: 6 */
/* jshint browser: true */

import React from 'react';
import ReactDOM from 'react-dom';
import MoneyTransfers from './components/money-transfers';

const transfers = [
  {
    id: 0,
    amount: null,
    accountFrom: null,
    accountTo: null,
  },
];

ReactDOM.render(<MoneyTransfers transfers={transfers} />, document.querySelector('#transfers'));
