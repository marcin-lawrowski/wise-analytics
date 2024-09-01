import React from "react";
import PropTypes from 'prop-types';
import { connect } from "react-redux";
import {Route, Routes} from "react-router-dom";
import Home from "./home/Home";
import Visitor from "./visitor/Visitor";
import Browse from "./browse/Browse";

class Visitors extends React.Component {

	render() {
		return <Routes>
			<Route path="/">
				<Route path="overview" element={<Home startDate={ this.props.startDate } endDate={ this.props.endDate } />} />
				<Route path="browse" element={<Browse startDate={ this.props.startDate } endDate={ this.props.endDate } />} />
				<Route path="browse/visitor/:id" element={<Visitor startDate={ this.props.startDate } endDate={ this.props.endDate } />} />=
			</Route>
		</Routes>
	}
}

Visitors.propTypes = {
	configuration: PropTypes.object.isRequired,
	startDate: PropTypes.object.isRequired,
	endDate: PropTypes.object.isRequired
};

export default connect(
	(state) => ({
		configuration: state.configuration
	})
)(Visitors);