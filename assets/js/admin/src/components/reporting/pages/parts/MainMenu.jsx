import React from "react";
import PropTypes from 'prop-types';
import { connect } from "react-redux";
import { Link } from "react-router-dom";
import { withRouter } from 'utils/router';

class MainMenu extends React.Component {

	render() {
		let section = 'overview';
		if (this.props.location.pathname.match(/^\/visitors/)) {
			section = 'visitors';
		}
		if (this.props.location.pathname.match(/^\/actions/)) {
			section = 'actions';
		}
		if (this.props.location.pathname.match(/^\/conversions/)) {
			section = 'conversions';
		}

		const linkClass = (path) => {
			return this.props.location.pathname.match('^' + path) ? 'd-block wa-bg-color-secondary-light text-muted' : 'd-block text-muted';
		}

		return <React.Fragment>
			<div className="card p-1">
				<div className="card-body main-menu">
					<Link className="d-block w-100 text-start top-item text-muted" to="/">Overview</Link>

					<Link className="d-block w-100 text-start top-item text-muted" to="/visitors/overview"><i className={ section !== 'visitors' ? "bi bi-chevron-right" : "bi bi-chevron-down" }/>Visitors</Link>

					<div className={ section !== 'visitors' ? 'd-none' : ''}>
						<ul className="mb-1">
							<li><Link to="/visitors/overview" className={ linkClass("/visitors/overview") }>Overview</Link></li>
							<li><Link to="/visitors/browse" className={ linkClass("/visitors/browse") }>Browse</Link></li>
							<li><Link to="/visitors/sources" className={ linkClass("/visitors/sources") }>Sources</Link></li>
						</ul>
					</div>

					<Link className="d-block w-100 text-start top-item text-muted" to="/actions/overview"><i className={ section !== 'actions' ? "bi bi-chevron-right" : "bi bi-chevron-down" }/>Actions</Link>

					<div className={ section !== 'actions' ? 'd-none' : ''}>
						<ul className="mb-1">
							<li><Link to="/actions/overview" className={ linkClass("/actions/overview") }>Overview</Link></li>
						</ul>
					</div>

					<Link className="d-block w-100 text-start top-item text-muted" to="/conversions/overview"><i className={ section !== 'conversions' ? "bi bi-chevron-right" : "bi bi-chevron-down" }/>Conversions</Link>

					<div className={ section !== 'conversions' ? 'd-none' : ''}>
						<ul className="mb-1">
							<li><Link to="/conversions/overview" className={ linkClass("/conversions/overview") }>Overview</Link></li>
						</ul>
					</div>

				</div>
			</div>
		</React.Fragment>

		return <div className="list-group mt-4">

			<button type="button" className="list-group-item list-group-item-action" data-bs-toggle="collapse" data-bs-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">A second item</button>


			<div className="collapse show list-group" id="collapseExample">
				<a className="list-group-item list-group-item-action" href="#">Aaaa</a>
				<a className="list-group-item list-group-item-action" href="#">BBB</a>

			</div>

		</div>


		return <ul className="list-group">
			<li className="list-group-item"><Link to="/" className={ "list-group-item list-group-item-action " + (current === 'overview' ? 'list-group-item-primary' : '') }>Overview</Link></li>
			<li className="list-group-item">
				<ul className="list-group">
					<li className="list-group-item">An item</li>
					<li className="list-group-item">A second item</li>
					<li className="list-group-item">A third item</li>
					<li className="list-group-item">A fourth item</li>
					<li className="list-group-item">And a fifth one</li>
				</ul>

			</li>
			<li className="list-group-item">A third item</li>
			<li className="list-group-item">A fourth item</li>
			<li className="list-group-item">And a fifth one</li>
		</ul>;

		return <div className="list-group mt-4">
			<Link to="/" className={ "list-group-item list-group-item-action " + (current === 'overview' ? 'list-group-item-primary' : '') }>Overview</Link>
			<Link to="/visitors" className={ "list-group-item list-group-item-action " + (current === 'visitors' ? 'list-group-item-primary' : '') }>Visitors</Link>
			<div className="list-group">
				<Link to="/visitors/overview" className={ "list-group-item list-group-item-action " + (current === 'visitors' ? 'list-group-item-primary' : '') }>Overview</Link>
				<Link to="/visitors/sources" className={ "list-group-item list-group-item-action " + (current === 'visitors' ? 'list-group-item-primary' : '') }>Sources</Link>
			</div>
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