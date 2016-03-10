import axios from 'axios';

export const NO_TRANSFERS = 'NO_TRANSFERS';

export function hasNoTransfers(reportId, noTransfers) {
    const url = `/report/${reportId}/notransfers`;
    axios.put(url, { noTransfers });
    return {
        type: NO_TRANSFERS,
        payload: noTransfers,
    };
}
