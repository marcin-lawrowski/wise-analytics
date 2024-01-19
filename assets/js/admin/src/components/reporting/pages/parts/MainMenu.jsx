import React from "react";
import PropTypes from 'prop-types';
import { connect } from "react-redux";
import { Link } from "react-router-dom";

class MainMenu extends React.Component {

	render() {
		return <div className="list-group mt-4">
			<Link to="/" className="list-group-item list-group-item-action">Overview</Link>
			<Link to="/visitors" className="list-group-item list-group-item-action">Visitors</Link>
		</div>
	}

}

MainMenu.propTypes = {
	configuration: PropTypes.object.isRequired
};

export default connect(
	(state) => ({
		configuration: state.configuration
	})
)(MainMenu);