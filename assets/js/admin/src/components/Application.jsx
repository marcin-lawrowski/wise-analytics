import React from "react";
import PropTypes from 'prop-types';
import { connect } from "react-redux";
import Overview from "./reporting/pages/Overview";

class Application extends React.Component {

	render() {
		return <div className="container-fluid" data-bs-theme="light">
			<Overview />
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