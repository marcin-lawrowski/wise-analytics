import React from "react";
import PropTypes from 'prop-types';
import { connect } from "react-redux";
import Highlights from "./Highlights";
import moment from 'moment';

class Overview extends React.Component {

	render() {
		return <React.Fragment>
			<h4>Overview</h4>
			<div className="row">
				<div className="col">
					<Highlights startDate={ moment().subtract(6, 'days').format('YYYY-MM-DD') } endDate={ moment().format('YYYY-MM-DD') } />
				</div>
			</div>
		</React.Fragment>
	}
}

Overview.propTypes = {
	configuration: PropTypes.object.isRequired
};

export default connect(
	(state) => ({
		configuration: state.configuration
	})
)(Overview);