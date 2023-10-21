import React from "react";
import PropTypes from 'prop-types';
import { connect } from "react-redux";
import { requestReport } from "actions/reports";
import moment from 'moment';
import Spinner from "common/Spinner";

class TopPages extends React.Component {

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
			name: 'pages.top',
			filters: {
				startDate: moment(this.props.startDate).format('YYYY-MM-DD'),
				endDate: moment(this.props.endDate).format('YYYY-MM-DD')
			}
		});
	}


	render() {
		return <React.Fragment>
			<h5 className="d-flex">Top Pages <Spinner show={ this.props.loading } /></h5>

			<div className="card p-1">
				<div className="card-body text-center">
					<table className="table table-striped">
						<thead>
							<tr>
								<th scope="col">Page</th>
								<th scope="col">Views</th>
							</tr>
						</thead>
						<tbody>
						{ this.props.report.pages.map( (page, index) =>
							<tr key={ index }>
								<td><a href={ this.props.configuration.baseUrl + page.uri } target="_blank">{ page.title ? page.title : page.uri }</a></td>
								<td>{ page.pageViews }</td>
							</tr>
						)}
						</tbody>
					</table>
				</div>
			</div>
		</React.Fragment>
	}
}

TopPages.propTypes = {
	configuration: PropTypes.object.isRequired,
	startDate: PropTypes.object,
	endDate: PropTypes.object
};

export default connect(
	(state) => ({
		configuration: state.configuration,
		loading: state.reports['pages.top'].inProgress,
		report: state.reports['pages.top'].result
	}), { requestReport }
)(TopPages);