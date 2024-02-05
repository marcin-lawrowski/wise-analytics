import React from "react";
import PropTypes from 'prop-types';
import { connect } from "react-redux";
import { setTitle } from "actions/ui";
import SourcesLineChart from "reporting/components/sessions/SourcesLineChart";

class Sources extends React.Component {

	componentDidMount() {
		this.props.setTitle('Visitors Sources');
	}

	render() {
		return <React.Fragment>
			<div className="row">
				<div className="col-md-12">
					<SourcesLineChart startDate={ this.props.startDate } endDate={ this.props.endDate } />
				</div>
			</div>
		</React.Fragment>;
	}
}

Sources.propTypes = {
	configuration: PropTypes.object.isRequired,
	startDate: PropTypes.object.isRequired,
	endDate: PropTypes.object.isRequired
};

export default connect(
	(state) => ({
		configuration: state.configuration
	}), { setTitle }
)(Sources);