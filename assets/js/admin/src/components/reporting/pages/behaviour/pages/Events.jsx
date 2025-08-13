import React from "react";
import PropTypes from 'prop-types';
import { connect } from "react-redux";
import { setTitle } from "actions/ui";
import EventsTable from "reporting/components/events/Events";

class Events extends React.Component {

	componentDidMount() {
		this.props.setTitle('Events');
	}

	render() {
		return <React.Fragment>
			<div className="row">
				<div className="col-md-12">
					<EventsTable
						startDate={ this.props.startDate }
						endDate={ this.props.endDate }
					/>
				</div>
			</div>
		</React.Fragment>;
	}
}

Events.propTypes = {
	configuration: PropTypes.object.isRequired,
	startDate: PropTypes.object.isRequired,
	endDate: PropTypes.object.isRequired
};

export default connect(
	(state) => ({
		configuration: state.configuration
	}), { setTitle }
)(Events);