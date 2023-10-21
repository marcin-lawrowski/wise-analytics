import React from "react";
import PropTypes from 'prop-types';
import { connect } from "react-redux";
import { requestReport } from "actions/reports";
import moment from 'moment';
import Loader from "common/Loader";

class Events extends React.Component {

	componentDidMount() {
		this.refresh();
	}

	componentDidUpdate(prevProps, prevState, snapshot) {
		if ((prevProps.startDate !== this.props.startDate || prevProps.endDate !== this.props.endDate) && this.props.startDate && this.props.endDate) {
			this.refresh();
		}
	}

	refresh() {
		this.props.requestReport({
			name: 'events',
			filters: {
				startDate: moment(this.props.startDate).format('YYYY-MM-DD'),
				endDate: moment(this.props.endDate).format('YYYY-MM-DD')
			}
		});
	}

	render() {
		return <React.Fragment>
			<div className="card p-1 w-100">
				<div className="card-body">
					<h5 className="card-title">Recent Events <Loader show={ this.props.loading } /></h5>
					<table className="table table-striped ">
						<thead>
							<tr>
								<th scope="col">Visitor</th>
								<th scope="col">Event</th>
								<th scope="col">URI</th>
								<th scope="col">Date</th>
							</tr>
						</thead>
						<tbody>
						{ this.props.report.events.map( (event, index) =>
							<tr key={ index }>
								<td>Visitor #{ event.visitorId }</td>
								<td>{ event.typeId === '1' ? 'Page View' : 'Unknown' }</td>
								<td>{ event.uri }</td>
								<td>{ event.created }</td>
							</tr>
						)}
						</tbody>
					</table>
				</div>
			</div>
		</React.Fragment>
	}
}

Events.propTypes = {
	configuration: PropTypes.object.isRequired,
	startDate: PropTypes.object,
	endDate: PropTypes.object
};

export default connect(
	(state) => ({
		configuration: state.configuration,
		loading: state.reports['events'].inProgress,
		report: state.reports['events'].result
	}), { requestReport }
)(Events);