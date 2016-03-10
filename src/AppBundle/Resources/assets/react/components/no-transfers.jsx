import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import { hasNoTransfers } from '../actions/transfers_actions';


class NoTranfers extends Component {

    onChange = () => {
        this.props.hasNoTransfers(1, !this.props.noTransfers);
    }

    render() {
        let labelClass = 'block-label';
        if (this.props.noTransfers) {
            labelClass += ' selected';
        }

        return (
            <div id="no-transfers">
                <h3 className="heading heading-small">
                    Didn't make any money transfers?
                </h3>
                <div id="form-group-report_noTransfersToAdd" className="form-group form-group__checkbox ">
                    <label className={labelClass} htmlFor="report_noTransfersToAdd">
                        <input
                            type="checkbox"
                            id="report_noTransfersToAdd"
                            name="report[noTransfersToAdd]"
                            checked={this.props.noTransfers}
                            onChange={this.onChange}
                        />
                        Tick this box if you didn't move any money between [FNAME]’s accounts
                        between 1 October 2014 and 1 October 2015
                    </label>

                </div>
            </div>
        );
    }
}

NoTranfers.propTypes = {
    noTransfers: React.PropTypes.bool,
    hasNoTransfers: React.PropTypes.func,
};

function mapStateToProps({ noTransfers }) {
    return { noTransfers };
}

function mapDispatchToProps(dispatch) {
    return bindActionCreators({ hasNoTransfers }, dispatch);
}

export default connect(mapStateToProps, mapDispatchToProps)(NoTranfers);
