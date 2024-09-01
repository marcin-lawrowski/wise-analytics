import React from "react";
import PropTypes from 'prop-types';
import { connect } from "react-redux";
import { requestReport, clearReport } from "actions/reports";
import moment from 'moment';
import LineChart from "common/charts/LineChart";
import Loader from "common/Loader";

class CategoriesDailyLineChart extends React.Component {

	componentDidMount() {
		this.refresh();
	}

	componentDidUpdate(prevProps, prevState, snapshot) {
		if ((prevProps.startDate !== this.props.startDate || prevProps.endDate !== this.props.endDate) && this.props.startDate && this.props.endDate) {
			this.refresh();
		}
	}

	componentWillUnmount() {
		this.props.clearReport('sources.categories.daily');
	}

	refresh() {
		this.props.requestReport({
			name: 'sources.categories.daily',
			filters: {
				startDate: moment(this.props.startDate).format('YYYY-MM-DD'),
				endDate: moment(this.props.endDate).format('YYYY-MM-DD')
			}
		});
	}

	render() {
		const lines = this.props.report.sourceCategories.length ? Object.keys(this.props.report.sourceCategories[0]).filter( line => line !== 'date' ) : [];

		const data = lines.map( categoryName => ({
			id: categoryName,
			single: categoryName,
			plural: categoryName,
			data: this.props.report.sourceCategories.map( (record, index) => ({ "x": record.date, "y": record[categoryName] }) )
		}));

		return <div className="card">
			<div className="card-body p-0">
				<h6 className="card-title text-muted">Source Categories Daily <Loader show={ this.props.loading } /></h6>

				<div style={ { height: 300 }}>
					{ this.props.report.sourceCategories.length > 0 && <LineChart data={ data } enableArea={ false } /> }
				</div>
			</div>
		</div>
	}
}

CategoriesDailyLineChart.propTypes = {
	configuration: PropTypes.object.isRequired,
	startDate: PropTypes.object,
	endDate: PropTypes.object
};

export default connect(
	(state) => ({
		configuration: state.configuration,
		loading: state.reports['sources.categories.daily'].inProgress,
		report: state.reports['sources.categories.daily'].result
	}), { requestReport, clearReport }
)(CategoriesDailyLineChart);