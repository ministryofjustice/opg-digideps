import { combineReducers } from 'redux';
import TransfersReducer from './transfers_reducer';
import AccountsReducer from './accounts_reducer';

const rootReducer = combineReducers({
  transfers: TransfersReducer,
  accounts: AccountsReducer,
});

export default rootReducer;
