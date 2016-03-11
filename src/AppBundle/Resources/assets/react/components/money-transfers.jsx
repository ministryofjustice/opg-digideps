import React, { Component } from 'react';
import { connect } from 'react-redux';
import { getTransfers, updateTransfer, deleteTransfer, saveTransfer } from '../actions/transfers_actions';
import { setActiveTransfer, clearActiveTransfer } from '../actions/active_transfer_actions';
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
          saveTransfer={this.props.saveTransfer}
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
    saveTransfer: React.PropTypes.func,
    activeTransfer: React.PropTypes.object,
    setActiveTransfer: React.PropTypes.func,
    clearActiveTransfer: React.PropTypes.func,
    report: React.PropTypes.object,
};

function mapStateToProps({ transfers, activeTransfer, report }) {
    return { transfers, activeTransfer, report };
}

export default connect(mapStateToProps, { getTransfers, updateTransfer, deleteTransfer, saveTransfer, setActiveTransfer, clearActiveTransfer })(MoneyTransfers);
