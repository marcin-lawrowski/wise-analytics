import React from "react";
import PropTypes from 'prop-types';
import { connect } from "react-redux";
import {Route, Routes} from "react-router-dom";
import Pages from "./pages/Pages";
import EntryPages from "./pages/EntryPages";
import ExitPages from "./pages/ExitPages";

class Behaviour extends React.Component {

	render() {
		return <Routes>
			<Route path="/">
				<Route path="pages" element={<Pages startDate={ this.props.startDate } endDate={ this.props.endDate } />} />
				<Route path="entry-pages" element={<EntryPages startDate={ this.props.startDate } endDate={ this.props.endDate } />} />
				<Route path="exit-pages" element={<ExitPages startDate={ this.props.startDate } endDate={ this.props.endDate } />} />
			</Route>
		</Routes>
	}
}

Behaviour.propTypes = {
	configuration: PropTypes.object.isRequired,
	startDate: PropTypes.object.isRequired,
	endDate: PropTypes.object.isRequired
};

export default connect(
	(state) => ({
		configuration: state.configuration
	})
)(Behaviour);