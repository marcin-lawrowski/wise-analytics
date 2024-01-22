import React from "react";
import PropTypes from 'prop-types';
import { connect } from "react-redux";
import { Link } from "react-router-dom";
import { withRouter } from 'utils/router';

class MainMenu extends React.Component {

	render() {
		let current = 'overview';
		if (this.props.location.pathname.match(/^\/visitors/)) {
			current = 'visitors';
		}

		return <div className="list-group mt-4">
			<Link to="/" className={ "list-group-item list-group-item-action " + (current === 'overview' ? 'list-group-item-primary' : '') }>Overview</Link>
			<Link to="/visitors" className={ "list-group-item list-group-item-action " + (current === 'visitors' ? 'list-group-item-primary' : '') }>Visitors</Link>
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
)(withRouter(MainMenu));