import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import { createStore, applyMiddleware } from 'redux';
import promise from './middleware/promise_middleware';
import thunk from 'redux-thunk';
import MoneyTransfers from './components/money-transfers';
import Saving from './components/saving';
import reducers from './reducers';

const createStoreWithMiddleware = applyMiddleware(
  promise, thunk
)(createStore);

const store = createStoreWithMiddleware(reducers);

ReactDOM.render(
  <Provider store={store}>
    <MoneyTransfers />
  </Provider>,
  document.querySelector('#transfers-container'));

ReactDOM.render(
  <Provider store={store}>
    <Saving />
  </Provider>,
  document.querySelector('.info'));
