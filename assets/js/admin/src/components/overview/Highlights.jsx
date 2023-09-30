import React from "react";
import PropTypes from 'prop-types';
import { connect } from "react-redux";
import { requestReport } from "actions/reports";
import Loader from "common/Loader";
import Spinner from "common/Spinner";

class Highlights extends React.Component {

	componentDidMount() {
		this.props.requestReport({
			name: 'overview.highlights',
			filters: {
				startDate: this.props.startDate,
				endDate: this.props.endDate
			}
		});
	}

	render() {
		return <React.Fragment>
			<h5 className="d-flex">Highlights <Spinner show={ this.props.loading } /></h5>

			<div className="row">
				<div className="col">
					<div className="card p-1">
						<div className="card-body text-center">
							<h6 className="card-title text-muted">Users</h6>
							<h3>{ this.props.report.users }</h3>
						</div>
					</div>
				</div>
				<div className="col">
					<div className="card p-1">
						<div className="card-body text-center">
							<h6 className="card-title text-muted">Page Views</h6>
							<h3>{ this.props.report.pageViews }</h3>
						</div>
					</div>
				</div>
				<div className="col">
					<div className="card p-1">
						<div className="card-body text-center">
							<h6 className="card-title text-muted">Pages / visit</h6>
							<h3>{ this.props.report.avgPagesPerVisit }</h3>
						</div>
					</div>
				</div>
			</div>
		</React.Fragment>
	}
}

Highlights.propTypes = {
	configuration: PropTypes.object.isRequired,
	startDate: PropTypes.string.isRequired,
	endDate: PropTypes.string.isRequired
};

export default connect(
	(state) => ({
		configuration: state.configuration,
		loading: state.reports['overview.highlights'].inProgress,
		report: state.reports['overview.highlights'].result
	}), { requestReport }
)(Highlights);