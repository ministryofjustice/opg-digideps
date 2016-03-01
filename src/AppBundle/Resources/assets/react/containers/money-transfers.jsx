import React, { Component } from 'react';
import { connect } from 'react-redux';
import { createTransfer, updateTransfer, deleteTransfer, getTransfers } from '../actions/transfers_actions';
import { bindActionCreators } from 'redux';
import MoneyTransfer from '../components/money-transfer';

class MoneyTransfers extends Component {

    componentWillMount() {
        this.props.getTransfers(1);
    }

    renderTransfers = (transfer) => {
        return (<MoneyTransfer
          transfer={transfer}
          key={transfer.id}
          createTransfer={this.props.createTransfer}
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
    getTransfers: React.PropTypes.func,
    createTransfer: React.PropTypes.func,
    updateTransfer: React.PropTypes.func,
    deleteTransfer: React.PropTypes.func,
};

function mapStateToProps({ transfers }) {
    return { transfers };
}

function mapDispatchToProps(dispatch) {
    return bindActionCreators({ getTransfers, createTransfer, updateTransfer, deleteTransfer }, dispatch);
}

export default connect(mapStateToProps, mapDispatchToProps)(MoneyTransfers);
