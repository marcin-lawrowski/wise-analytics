import React from "react";
import PropTypes from 'prop-types';
import { connect } from "react-redux";
import { setTitle } from "actions/ui";
import SourcesLineChart from "reporting/components/sessions/SourcesLineChart";
import SourcesCategoryTable from "reporting/components/sessions/SourcesCategoryTable";
import SourcesChart from "reporting/components/sessions/SourcesChart";

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
			<div className="row">
				<div className="col-md-9">
					<SourcesCategoryTable startDate={ this.props.startDate } endDate={ this.props.endDate } />
				</div>
				<div className="col-md-3">
					<SourcesChart startDate={ this.props.startDate } endDate={ this.props.endDate } />
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