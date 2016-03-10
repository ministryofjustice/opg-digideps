import { combineReducers } from 'redux';
import TransfersReducer from './transfers_reducer';
import AccountsReducer from './accounts_reducer';
import ActiveTransferReducer from './active_transfer_reducer';
import NoTransfersReducer from './no_transfers_reducer';
import ReportReducer from './report_reducer';
import SavingReducer from './saving_reducer';

const rootReducer = combineReducers({
    transfers: TransfersReducer,
    accounts: AccountsReducer,
    activeTransfer: ActiveTransferReducer,
    noTransfers: NoTransfersReducer,
    report: ReportReducer,
    saving: SavingReducer,
});

export default rootReducer;
