import React from "react";
import PropTypes from 'prop-types';
import { connect } from "react-redux";
import { requestReport } from "actions/reports";
import moment from 'moment';
import Spinner from "common/Spinner";

class Visitors extends React.Component {

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
			name: 'visitors',
			filters: {
				startDate: moment(this.props.startDate).format('YYYY-MM-DD'),
				endDate: moment(this.props.endDate).format('YYYY-MM-DD')
			}
		});
	}

	render() {
		return <React.Fragment>
			<h5 className="d-flex">Visitors <Spinner show={ this.props.loading } /></h5>

			<div className="card p-1">
				<div className="card-body text-center">
					<table className="table table-striped">
						<thead>
							<tr>
								<th scope="col">Name</th>
								<th scope="col">Visits</th>
								<th scope="col">Avg. Visit</th>
								<th scope="col">Last Visit</th>
							</tr>
						</thead>
						<tbody>
						{ this.props.report.visitors.map( (visitor, index) =>
							<tr key={ index }>
								<td>User #{ visitor.id }</td>
								<td>{ visitor.totalSessions }</td>
								<td>{ visitor.avgSessionDuration }</td>
								<td>{ visitor.lastVisit }</td>
							</tr>
						)}
						</tbody>
					</table>
				</div>
			</div>
		</React.Fragment>
	}
}

Visitors.propTypes = {
	configuration: PropTypes.object.isRequired,
	startDate: PropTypes.object,
	endDate: PropTypes.object
};

export default connect(
	(state) => ({
		configuration: state.configuration,
		loading: state.reports['visitors'].inProgress,
		report: state.reports['visitors'].result
	}), { requestReport }
)(Visitors);