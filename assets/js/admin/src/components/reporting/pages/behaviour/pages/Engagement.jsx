import React from "react";
import PropTypes from 'prop-types';
import { connect } from "react-redux";
import { setTitle } from "actions/ui";
import VisitsByNumber from "reporting/components/behaviour/VisitsByNumber";

class Engagement extends React.Component {

	componentDidMount() {
		this.props.setTitle('Engagement');
	}

	render() {
		return <React.Fragment>
			<div className="row">
				<div className="col-md-12">
					<VisitsByNumber startDate={ this.props.startDate } endDate={ this.props.endDate } />
				</div>
			</div>
		</React.Fragment>;
	}
}

Engagement.propTypes = {
	configuration: PropTypes.object.isRequired,
	startDate: PropTypes.object.isRequired,
	endDate: PropTypes.object.isRequired
};

export default connect(
	(state) => ({
		configuration: state.configuration
	}), { setTitle }
)(Engagement);