import React, { Component } from 'react';

const AccountCard =({account, selectAccount}) => {
    
    return (
        <div className="card-item">
            <div className="card" onClick={() => selectAccount(account)}>
                <div className="account card-title">{account.name} {account.type}</div>
                <dl className="labelvalue">
                    <dt className="label">Account number:</dt>
                    <dd className="value">0000{account.accountNumber}</dd>
                    <dt className="label">Sort code:</dt>
                    <dd className="value">{account.sortCode}</dd>
                </dl>
            </div>
        </div>
    );

};


export default AccountCard;
