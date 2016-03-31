import React, { Component } from 'react';
import { connect } from 'react-redux';
import { SAVE_TRANSFER, SAVED_TRANSFER, SAVE_TRANSFER_ERROR } from '../actions/transfers_actions';

const status = {
    'NOTHING': {
        label: '',
        state: ''
    },
    SAVE_TRANSFER: {
        label: 'Saving...',
        state: 'saving'
    },
    SAVED_TRANSFER: {
        label: 'Saved',
        state: 'saved'
    },
    SAVE_TRANSFER_ERROR: {
        label: 'Not saved',
        state: 'notsaved'
    }
};


class Saving extends Component {
    render() {
        return (
            <span id="save-status" data-status="{ status[this.props.saving].state }">
                { status[this.props.saving].label }
            </span>
        );
    }
}

Saving.propTypes = {
    saving: React.PropTypes.string,
};

function mapStateToProps({ saving }) {
    return { saving };
}


export default connect(mapStateToProps)(Saving);
