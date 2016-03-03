import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import { createStore, applyMiddleware } from 'redux';
import promise from './middleware/promise_middleware';
import thunk from 'redux-thunk';
import MoneyTransfers from './containers/money-transfers';
import reducers from './reducers';

const createStoreWithMiddleware = applyMiddleware(
  promise, thunk
)(createStore);

ReactDOM.render(
  <Provider store={createStoreWithMiddleware(reducers)}>
    <MoneyTransfers />
  </Provider>,
  document.querySelector('#transfers-container'));
