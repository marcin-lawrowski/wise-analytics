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

	renderVisitor(event) {
		let name = [event.visitorFirstName, event.visitorLastName].join(' ').trim();
		if (!name) {
			name = 'Visitor #' + event.visitorId;
		}

		return name;
	}

	render() {
		return <React.Fragment>
			<div className="card p-1 w-100">
				<div className="card-body">
					<h6 className="card-title">Recent Events <Loader show={ this.props.loading } /></h6>
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
								<td>{ this.renderVisitor(event) }</td>
								<td>{ event.typeName ? event.typeName : 'Unknown' }</td>
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