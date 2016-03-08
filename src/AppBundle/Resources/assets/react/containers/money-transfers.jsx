import React, { Component } from 'react';
import { connect } from 'react-redux';
import { createTransfer, updateTransfer, deleteTransfer, getTransfers } from '../actions/transfers_actions';
import { setActiveTransfer, clearActiveTransfer } from '../actions/active_transfer_actions';
import { bindActionCreators } from 'redux';
import MoneyTransfer from '../components/money-transfer';

class MoneyTransfers extends Component {

    componentWillMount() {
        this.props.getTransfers(1);
    }

    renderTransfers = (transfer, index) => {
        const active = (transfer === this.props.activeTransfer);

        return (<MoneyTransfer
          transfer={transfer}
          key={index}
          createTransfer={this.props.createTransfer}
          updateTransfer={this.props.updateTransfer}
          deleteTransfer={this.props.deleteTransfer}
          setActiveTransfer={this.props.setActiveTransfer}
          clearActiveTransfer={this.props.clearActiveTransfer}
          active={active}
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
    activeTransfer: React.PropTypes.bool,
    setActiveTransfer: React.PropTypes.func,
    clearActiveTransfer: React.PropTypes.func,
};

function mapStateToProps({ transfers, activeTransfer }) {
    return { transfers, activeTransfer };
}

function mapDispatchToProps(dispatch) {
    return bindActionCreators({
        getTransfers, createTransfer,
        updateTransfer, deleteTransfer,
        setActiveTransfer, clearActiveTransfer
    }, dispatch);
}

export default connect(mapStateToProps, mapDispatchToProps)(MoneyTransfers);
