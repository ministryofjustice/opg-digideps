import React, { Component } from 'react';

const AccountCard =({account, selectAccount}) => {
    
    return (
        <div className="card-item">
            <div className="card" onClick={() => selectAccount(account)}>
                <div className="account card-title">{account.bank} {account.account_type}</div>
                <dl className="labelvalue">
                    <dt className="label">Account number:</dt>
                    <dd className="value">0000{account.account_number}</dd>
                    <dt className="label">Sort code:</dt>
                    <dd className="value">{account.sort_code}</dd>
                </dl>
            </div>
        </div>
    );

};


export default AccountCard;
