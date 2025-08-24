import React from "react";
import PropTypes from 'prop-types';
import {connect} from "react-redux";
import Select from "react-select";
import Loader from "common/Loader";
import {requestReport} from "actions/reports";
import moment from "moment/moment";
import ComparisonLineChart from "common/charts/ComparisonLineChart";
import { getDuration } from "utils/dates";

class LeadLineChart extends React.Component {

	get STATS() {
		return [
			{ value: 'visitors', label: 'Visitors', labelSingle: 'Visitor', reportKey: 'visitors', stat: 'visitors' },
			{ value: 'sessions', label: 'Visits', labelSingle: 'Visit', reportKey: 'sessions', stat: 'sessions' },
			{ value: 'sessions.avg.time', label: 'Average Time', labelSingle: 'Average Time', reportKey: 'sessions', stat: 'time', formatter: getDuration },
			{ value: 'pages.views', label: 'Page Views', labelSingle: 'Page View', reportKey: 'pageViews', stat: 'pageViews' }
		];
	}

	get DEFAULT_STAT() {
		return 'visitors';
	}

	get PERIOD_OPTIONS() {
		return [
			{ value: undefined, label: 'daily' },
			{ value: 'weekly', label: 'weekly' },
			{ value: 'monthly', label: 'monthly' },
		]
	}

	constructor(props) {
		super(props);

		const statRecord = this.STATS.find( stat => stat.value === this.DEFAULT_STAT );

		this.state = {
			stats: this.DEFAULT_STAT,
			compareStats: undefined,
			period: undefined,
			loading: false,
			chartData: [{
				id: statRecord.label,
				single: statRecord.labelSingle,
				plural: statRecord.label,
				data: []
			}]
		}

		this.convertReportRecord = this.convertReportRecord.bind(this);
	}

	componentDidMount() {
		this.refresh();
	}

	componentDidUpdate(prevProps, prevState, snapshot) {
		if ((prevProps.startDate !== this.props.startDate || prevProps.endDate !== this.props.endDate) && this.props.startDate && this.props.endDate) {
			this.refresh();
		}
		if (this.props.report !== prevProps.report) {
			const statRecord = this.STATS.find( option => option.value === this.state.stats );

			const data = [{
				id: statRecord.label,
				single: statRecord.labelSingle,
				plural: statRecord.label,
				formatter: statRecord.formatter,
				data: this.props.report.length > 0 ? this.props.report[0][statRecord.reportKey].map( (record, index) => this.convertReportRecord(record, index, statRecord) ) : []
			}];

			if (this.props.report.length > 1) {
				const statRecordComparison = this.STATS.find( option => option.value === this.state.compareStats );

				data.push({
					id: statRecordComparison.label,
					single: statRecordComparison.labelSingle,
					plural: statRecordComparison.label,
					formatter: statRecordComparison.formatter,
					data: this.props.report[1][statRecordComparison.reportKey].map( (record, index) => this.convertReportRecord(record, index, statRecordComparison) )
				});
			}
			this.setState({ chartData: data });
		}
	}

	refresh() {
		this.props.requestReport({
			name: 'combined',
			reports: [this.state.stats, this.state.compareStats].filter( report => report ),
			filters: {
				startDate: moment(this.props.startDate).format('YYYY-MM-DD'),
				endDate: moment(this.props.endDate).format('YYYY-MM-DD')
			},
			modifiers: {
				period: this.state.period
			}
		});
	}

	convertReportRecord(record, index, statRecord) {
		return {
			"x": record.date,
			"y": record[statRecord.stat]
		};
	}

	getChartValue(statName) {

	}

	render() {
		const compareWithOptions = [ { value: undefined, label: 'Select ...'}, ...this.STATS.filter( statDef => statDef.value !== this.state.stats ) ];

		return <div className="card p-1">
			<div className="card-body">
				<h6 className="card-title">Overview</h6>
				<div className="row mb-3">
					<div className="col-auto">
						<Select
							className="me-2 w-100"
							value={ this.STATS.find( option => option.value === this.state.stats )}
							onChange={ selected => this.setState({ stats: selected.value, compareStats: this.state.compareStats !== selected.value ? this.state.compareStats : undefined }, this.refresh) }
							options={ this.STATS }
							isSearchable={ false }
						/>
					</div>
					<div className="col-auto d-flex align-items-center">
						compare with:
					</div>
					<div className="col-auto">
						<Select
							className="me-2 w-auto"
							value={ compareWithOptions.find( option => option.value === this.state.compareStats )}
							onChange={ selected => this.setState({ compareStats: selected.value }, this.refresh) }
							options={ compareWithOptions }
							isSearchable={ false }
						/>
					</div>
					<div className="col-auto">
						<Select
							className="me-2 w-auto"
							value={ this.PERIOD_OPTIONS.find( option => option.value === this.state.period )}
							onChange={ selected => this.setState({ period: selected.value }, this.refresh) }
							options={ this.PERIOD_OPTIONS }
							isSearchable={ false }
						/>
					</div>
					<div className="col-auto d-flex align-items-center">
						<Loader show={ this.props.loading } />
					</div>
				</div>
				<div style={ { height: 200 }}>
					{ this.props.report.length > 0 &&
						<ComparisonLineChart
							data={ this.state.chartData }
						/>
					}
				</div>
			</div>
		</div>
	}
}

LeadLineChart.propTypes = {
	configuration: PropTypes.object.isRequired,
	startDate: PropTypes.object,
	endDate: PropTypes.object
};

export default connect(
	(state) => ({
		configuration: state.configuration,
		loading: state.reports['combined'].inProgress,
		report: state.reports['combined'].result
	}), { requestReport }
)(LeadLineChart);