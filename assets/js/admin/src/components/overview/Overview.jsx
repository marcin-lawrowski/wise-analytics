import React from "react";
import PropTypes from 'prop-types';
import { connect } from "react-redux";
import Highlights from "./Highlights";
import moment from 'moment';
import DatePicker from "react-datepicker";
import TopPages from "./TopPages";
import Visitors from "../visitors/Visitors";
import Events from "../events/Events";
import VisitorsChart from "../visitors/VisitorsChart";
import Select from 'react-select';
import {getDatesRange} from "utils/dates";

class Overview extends React.Component {

	get RANGES() {
		return [
			{ value: 'today', label: 'Today', ...getDatesRange('today') },
			{ value: 'yesterday', label: 'Yesterday', ...getDatesRange('yesterday') },
			{ value: 'last7Days', label: 'This Week', ...getDatesRange('last7Days') },
			{ value: 'last14Days', label: 'Last 2 Weeks', ...getDatesRange('last14Days') },
			{ value: 'thisMonth', label: 'This Month', ...getDatesRange('thisMonth') }
		];
	}

	constructor(props) {
		super(props);

		this.state = {
			startDate: moment().subtract(13, 'days').toDate(),
			endDate: moment().toDate(),
			range: 'last14Days'
		}

		this.onDatesRangeChange = this.onDatesRangeChange.bind(this);
		this.onRangeChange = this.onRangeChange.bind(this);
	}

	onDatesRangeChange(dates, d) {
		this.setState({ startDate: dates[0], endDate: dates[1] });
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