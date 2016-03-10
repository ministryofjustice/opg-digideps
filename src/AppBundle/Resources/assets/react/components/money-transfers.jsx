import React, { Component } from 'react';
import { connect } from 'react-redux';
import { createTransfer, updateTransfer, deleteTransfer, getTransfers } from '../actions/transfers_actions';
import { setActiveTransfer, clearActiveTransfer } from '../actions/active_transfer_actions';
import { bindActionCreators } from 'redux';
import MoneyTransfer from '../components/money-transfer';
import NoTransfers from '../components/no-transfers';

class MoneyTransfers extends Component {

    componentWillMount() {
        this.props.getTransfers(this.props.report.id);
    }

    renderTransfers = (transfer, index) => {
        return (<MoneyTransfer
          transfer={transfer}
          key={index}
          createTransfer={this.props.createTransfer}
          updateTransfer={this.props.updateTransfer}
          deleteTransfer={this.props.deleteTransfer}
          setActiveTransfer={this.props.setActiveTransfer}
          clearActiveTransfer={this.props.clearActiveTransfer}
          activeTransfer={this.props.activeTransfer}
        />);
    }

    render() {
        return (
            <div>
                <ul id="transfers" className="card-list" data-count={this.props.transfers.length}>
                    {this.props.transfers.map(this.renderTransfers)}
                </ul>

                { (this.props.transfers.length) === 1 ?
                    <NoTransfers />
                    :
                    null }
            </div>
        );
    }
}

MoneyTransfers.propTypes = {
    transfers: React.PropTypes.array,
    getTransfers: React.PropTypes.func,
    createTransfer: React.PropTypes.func,
    updateTransfer: React.PropTypes.func,
    deleteTransfer: React.PropTypes.func,
    activeTransfer: React.PropTypes.object,
    setActiveTransfer: React.PropTypes.func,
    clearActiveTransfer: React.PropTypes.func,
    report: React.PropTypes.object,
};

function mapStateToProps({ transfers, activeTransfer, report }) {
    return { transfers, activeTransfer, report };
}

function mapDispatchToProps(dispatch) {
    return bindActionCreators({
        getTransfers, createTransfer,
        updateTransfer, deleteTransfer,
        setActiveTransfer, clearActiveTransfer
    }, dispatch);
}

export default connect(mapStateToProps, mapDispatchToProps)(MoneyTransfers);
