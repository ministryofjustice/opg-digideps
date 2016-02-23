import React, { Component } from 'react';
import { connect } from 'react-redux';

import MoneyTransfer from '../components/money-transfer';

class MoneyTransfers extends Component {

  renderTransfers(transfer) {
    return (<MoneyTransfer transfer={transfer} key={transfer.id} />);
  }

  render() {
    return (
        <ul id="transfers" className="card-list">
          {this.props.transfers.map(this.renderTransfers)}
        </ul>
    );
  }
}

MoneyTransfers.propTypes = {
  transfers: React.PropTypes.array,
};

function mapStateToProps({ transfers }) {
  return { transfers };
}

export default connect(mapStateToProps)(MoneyTransfers);
