import React from "react";
import PropTypes from 'prop-types';
import { connect } from "react-redux";
import Highlights from "reporting/components/overall/Highlights";
import moment from 'moment';
import DatePicker from "react-datepicker";
import TopPages from "reporting/components/pages/TopPages";
import Visitors from "reporting/components/visitors/Visitors";
import Events from "reporting/components/events/Events";
import VisitorsChart from "reporting/components/visitors/VisitorsChart";
import Select from 'react-select';
import {getDatesRange} from "utils/dates";

class Overview extends React.Component {

	get RANGES() {
		return [
			{ value: undefined, label: 'Custom' },
			{ value: 'today', label: 'Today', ...getDatesRange('today') },
			{ value: 'yesterday', label: 'Yesterday', ...getDatesRange('yesterday') },
			{ value: 'last7Days', label: 'Last Week', ...getDatesRange('last7Days') },
			{ value: 'last14Days', label: 'Last 2 Weeks', ...getDatesRange('last14Days') },
			{ value: 'last30Days', label: 'Last 30 Days', ...getDatesRange('last30Days') },
			{ value: 'thisMonth', label: 'This Month', ...getDatesRange('thisMonth') }
		];
	}

	constructor(props) {
		super(props);

		this.state = {
			...getDatesRange('last30Days'),
			range: 'last30Days'
		}

		this.onDatesRangeChange = this.onDatesRangeChange.bind(this);
		this.onRangeChange = this.onRangeChange.bind(this);
	}

	onDatesRangeChange(dates) {
		this.setState({ startDate: dates[0], endDate: dates[1], range: undefined });
	}

	onRangeChange(selected) {
		this.setState({ range: selected.value, startDate: selected.startDate, endDate: selected.endDate });
	}

	render() {
		return <React.Fragment>
			<div className="d-flex align-items-center justify-content-between">
				<h5>Analytics</h5>
				<div className="d-flex align-items-center">
					<Select
						value={ this.RANGES.find( option => option.value === this.state.range )}
						onChange={ this.onRangeChange }
						options={ this.RANGES }
						isSearchable={ false }
					/>
					&nbsp;
					<DatePicker
						selected={ this.state.startDate }
						onChange={ this.onDatesRangeChange }
						minDate={ moment().subtract(3, 'months').toDate() }
						maxDate={ new Date() }
						startDate={ this.state.startDate }
						endDate={ this.state.endDate }
						selectsRange
						className="form-control"
					/>
				</div>
			</div>
			<div className="row">
				<div className="col">
					<Highlights
						startDate={ this.state.startDate }
						endDate={ this.state.endDate }
					/>
				</div>
			</div>
			<div className="row mt-3">
				<div className="col-md-12">
					<VisitorsChart
						startDate={ this.state.startDate }
						endDate={ this.state.endDate }
					/>
				</div>
			</div>
			<div className="row mt-3">
				<div className="col-md-3">
					<TopPages
						startDate={ this.state.startDate }
						endDate={ this.state.endDate }
					/>
				</div>
				<div className="col-md-3">
					<Visitors
						startDate={ this.state.startDate }
						endDate={ this.state.endDate }
					/>
				</div>
				<div className="col-md-6">
					<Events
						startDate={ this.state.startDate }
						endDate={ this.state.endDate }
					/>
				</div>
			</div>
		</React.Fragment>
	}
}

Overview.propTypes = {
	configuration: PropTypes.object.isRequired
};

export default connect(
	(state) => ({
		configuration: state.configuration
	})
)(Overview);