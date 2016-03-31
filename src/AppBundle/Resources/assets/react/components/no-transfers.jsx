import React, { Component } from 'react';
import { connect } from 'react-redux';
import { hasNoTransfers } from '../actions/report_actions';


class NoTransfers extends Component {

    onChange = () => {
        this.props.hasNoTransfers(this.props.report.id, !this.props.report.noTransfers);
    }

    render() {
        let labelClass = 'block-label';
        if (this.props.report.noTransfers) {
            labelClass += ' selected';
        }
        const report = this.props.report;

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
                            checked={report.noTransfers}
                            onChange={this.onChange}
                        />
                        Tick this box if you didn't move any money between { report.client.firstname }’s accounts
                        between { report.startDate } and { report.endDate }
                    </label>

                </div>
            </div>
        );
    }
}

NoTransfers.propTypes = {
    report: React.PropTypes.object,
    hasNoTransfers: React.PropTypes.func,
};

function mapStateToProps({ report }) {
    return { report };
}

export default connect(mapStateToProps, { hasNoTransfers })(NoTransfers);
