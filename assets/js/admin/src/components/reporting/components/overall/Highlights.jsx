import React from "react";
import PropTypes from 'prop-types';
import { connect } from "react-redux";
import { requestReport } from "actions/reports";
import moment from 'moment';
import Loader from "common/Loader";

class Highlights extends React.Component {

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
			name: 'overview.highlights',
			filters: {
				startDate: moment(this.props.startDate).format('YYYY-MM-DD'),
				endDate: moment(this.props.endDate).format('YYYY-MM-DD')
			}
		});
	}


	render() {
		return <React.Fragment>
			<div className="row">
				<div className="col">
					<div className="card p-1">
						<div className="card-body text-center">
							<h6 className="card-title text-muted">Visitors <Loader show={ this.props.loading } /></h6>
							<h3>{ this.props.report.visitors.total } </h3>
							{ this.props.report.visitors.percentNew }% <span className="text-muted">new</span>
						</div>
					</div>
				</div>
				<div className="col">
					<div className="card p-1">
						<div className="card-body text-center">
							<h6 className="card-title text-muted">Page Views <Loader show={ this.props.loading } /></h6>
							<h3>{ this.props.report.pageViews }</h3>
						</div>
					</div>
				</div>
				<div className="col">
					<div className="card p-1">
						<div className="card-body text-center">
							<h6 className="card-title text-muted">Pages / visit <Loader show={ this.props.loading } /></h6>
							<h3>{ this.props.report.avgPagesPerVisit }</h3>
						</div>
					</div>
				</div>
				<div className="col">
					<div className="card p-1">
						<div className="card-body text-center">
							<h6 className="card-title text-muted">Avg. Time <Loader show={ this.props.loading } /></h6>
							<h3>{ this.props.report.avgSessionTime }</h3>
						</div>
					</div>
				</div>
			</div>
		</React.Fragment>
	}
}

Highlights.propTypes = {
	configuration: PropTypes.object.isRequired,
	startDate: PropTypes.object,
	endDate: PropTypes.object
};

export default connect(
	(state) => ({
		configuration: state.configuration,
		loading: state.reports['overview.highlights'].inProgress,
		report: state.reports['overview.highlights'].result
	}), { requestReport }
)(Highlights);