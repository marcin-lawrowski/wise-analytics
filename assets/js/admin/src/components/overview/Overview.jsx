import React from "react";
import PropTypes from 'prop-types';
import { connect } from "react-redux";
import Highlights from "./Highlights";
import moment from 'moment';
import DatePicker from "react-datepicker";
import TopPages from "./TopPages";
import Visitors from "../visitors/Visitors";
import Events from "../events/Events";

class Overview extends React.Component {

	constructor(props) {
		super(props);

		this.state = {
			startDate: moment().subtract(6, 'days').toDate(),
			endDate: moment().toDate()
		}

		this.onDatesRangeChange = this.onDatesRangeChange.bind(this);
	}

	onDatesRangeChange(dates, d) {
		this.setState({ startDate: dates[0], endDate: dates[1] });
	}

	render() {
		return <React.Fragment>
			<div className="d-flex align-items-center justify-content-between">
				<h4>Overview</h4>
				<div>
					Dates range:&nbsp;
					<DatePicker
						selected={ this.state.startDate }
						onChange={ this.onDatesRangeChange }
						minDate={ moment().subtract(3, 'months').toDate() }
						maxDate={ new Date() }
						startDate={ this.state.startDate }
						endDate={ this.state.endDate }
						selectsRange
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