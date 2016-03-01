import React, { Component } from 'react';
import { connect } from 'react-redux';
import { updateTransfer, deleteTransfer } from '../actions/index';
import { bindActionCreators } from 'redux';
import MoneyTransfer from '../components/money-transfer';

class MoneyTransfers extends Component {

    renderTransfers = (transfer) => {
        return (<MoneyTransfer
          transfer={transfer}
          key={transfer.id}
          updateTransfer={this.props.updateTransfer}
          deleteTransfer={this.props.deleteTransfer}
        />);
    }

    render() {
        return (
            <ul id="transfers" className="card-list" data-count={this.props.transfers.length}>
              {this.props.transfers.map(this.renderTransfers)}
            </ul>
        );
    }
}

MoneyTransfers.propTypes = {
    transfers: React.PropTypes.array,
    updateTransfer: React.PropTypes.func,
    deleteTransfer: React.PropTypes.func,
};

function mapStateToProps({ transfers }) {
    return { transfers };
}

function mapDispatchToProps(dispatch) {
    return bindActionCreators({ updateTransfer, deleteTransfer }, dispatch);
}

export default connect(mapStateToProps, mapDispatchToProps)(MoneyTransfers);
