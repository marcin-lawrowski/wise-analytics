import React from "react";
import PropTypes from 'prop-types';
import { connect } from "react-redux";
import { setTitle } from "actions/ui";
import ExternalPagesTable from "reporting/components/behaviour/ExternalPagesTable";

class ExternalPages extends React.Component {

	componentDidMount() {
		this.props.setTitle('Pages Views');
	}

	render() {
		return <React.Fragment>
			<div className="row">
				<div className="col-md-12">
					<ExternalPagesTable startDate={ this.props.startDate } endDate={ this.props.endDate } />
				</div>
			</div>
		</React.Fragment>;
	}
}

ExternalPages.propTypes = {
	configuration: PropTypes.object.isRequired,
	startDate: PropTypes.object.isRequired,
	endDate: PropTypes.object.isRequired
};

export default connect(
	(state) => ({
		configuration: state.configuration
	}), { setTitle }
)(ExternalPages);