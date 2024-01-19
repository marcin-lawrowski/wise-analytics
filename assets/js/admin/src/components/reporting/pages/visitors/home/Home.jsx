import React from "react";
import PropTypes from 'prop-types';
import { connect } from "react-redux";
import LanguagesChart from "reporting/components/visitors/LanguagesChart";

class Home extends React.Component {

	render() {
		return <React.Fragment>
			<div className="row">
				<div className="col-md-9">
					table
				</div>
				<div className="col-md-3">
					<LanguagesChart startDate={ this.props.startDate } endDate={ this.props.endDate } />
				</div>
			</div>
		</React.Fragment>;
	}
}

Home.propTypes = {
	configuration: PropTypes.object.isRequired,
	startDate: PropTypes.object.isRequired,
	endDate: PropTypes.object.isRequired
};

export default connect(
	(state) => ({
		configuration: state.configuration
	})
)(Home);