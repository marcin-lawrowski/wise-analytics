import React from "react";
import PropTypes from 'prop-types';
import { connect } from "react-redux";
import LanguagesChart from "reporting/components/visitors/LanguagesChart";
import DevicesChart from "reporting/components/visitors/DevicesChart";
import MainTable from "reporting/components/visitors/MainTable";
import SourcesChart from "reporting/components/sessions/SourcesChart";

class Home extends React.Component {

	render() {
		return <React.Fragment>
			<div className="row">
				<div className="col-md-9">
					<MainTable startDate={ this.props.startDate } endDate={ this.props.endDate } />
				</div>
				<div className="col-md-3">
					<SourcesChart startDate={ this.props.startDate } endDate={ this.props.endDate } />
					<DevicesChart startDate={ this.props.startDate } endDate={ this.props.endDate } />
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