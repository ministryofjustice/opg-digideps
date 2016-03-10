import React, { Component } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';


class Saving extends Component {
    render() {
        return (
            <div>{this.props.saving}</div>
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
