import { combineReducers } from 'redux';
import TransfersReducer from './transfers_reducer';
import AccountsReducer from './accounts_reducer';
import ActiveTransferReducer from './active_transfer_reducer';

const rootReducer = combineReducers({
    transfers: TransfersReducer,
    accounts: AccountsReducer,
    activeTransfer: ActiveTransferReducer,
});

export default rootReducer;
