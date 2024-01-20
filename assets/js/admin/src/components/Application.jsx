import React from "react";
import PropTypes from 'prop-types';
import { connect } from "react-redux";
import Overview from "./reporting/pages/Overview";
import { Routes, Route } from "react-router-dom";
import Visitors from "./reporting/pages/visitors/Visitors";
import {getDatesRange} from "utils/dates";
import DatesRangeFilter from "./reporting/pages/parts/DatesRangeFilter";
import MainMenu from "./reporting/pages/parts/MainMenu";

class Application extends React.Component {

	constructor(props) {
		super(props);

		this.state = {
			...getDatesRange('last30Days'),
			range: 'last30Days'
		}
	}

	render() {

		return <div className="container-fluid" data-bs-theme="light">
			<div className="d-flex align-items-center justify-content-between">
				<h5>Analytics: Overview</h5>
				<DatesRangeFilter
					onDatesRangeChange={ (startDate, endDate, range) => this.setState({ startDate: startDate, endDate: endDate, range: range }) }
					range={ this.state.range }
					startDate={ this.state.startDate }
					endDate={ this.state.endDate }
				/>
			</div>
			<div className="row">
				<div className="col-md-1">
					<MainMenu />
				</div>
				<div className="col-md-11 pt-4">
					<Routes>
						<Route path="/">
							<Route index element={ <Overview startDate={ this.state.startDate } endDate={ this.state.endDate } /> } />
							<Route path="visitors/*" element={<Visitors startDate={ this.state.startDate } endDate={ this.state.endDate } />} />
						</Route>
					</Routes>
				</div>
			</div>
		</div>
	}

}

Application.propTypes = {
	configuration: PropTypes.object.isRequired,
	rootElement: PropTypes.object.isRequired
};

export default connect(
	(state) => ({
		configuration: state.configuration
	})
)(Application);