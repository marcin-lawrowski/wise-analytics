import React from "react";
import PropTypes from 'prop-types';
import Loader from "common/Loader";

class StatsTable extends React.Component {

	constructor(props) {
		super(props);

		this.renderPaginationSummary = this.renderPaginationSummary.bind(this);
		this.handlePrev = this.handlePrev.bind(this);
		this.handleNext = this.handleNext.bind(this);
		this.hasNext = this.hasNext.bind(this);
		this.hasPrev = this.hasPrev.bind(this);
	}

	renderPaginationSummary() {
		if (!this.props.total) {
			return null;
		}

		let upperLimit = this.props.offset + this.props.limit + 1;
		if (upperLimit > this.props.total) {
			upperLimit = this.props.total;
		}

		return <div>
			{ this.props.offset + 1} - { upperLimit } / { this.props.total }
		</div>
	}

	handlePrev(e) {
		e.preventDefault();
		if (!this.hasPrev()) {
			return;
		}

		this.props.onOffsetChange(this.props.offset - this.props.limit);
	}

	handleNext(e) {
		e.preventDefault();
		if (!this.hasNext()) {
			return;
		}

		this.props.onOffsetChange(this.props.offset + this.props.limit);
	}

	hasNext() {
		const newOffset = this.props.offset + this.props.limit;

		return newOffset <= this.props.total;
	}

	hasPrev() {
		const newOffset = this.props.offset - this.props.limit;

		return newOffset >= 0;
	}

	render() {
		return <div className={ "card " + this.props.className }>
			<div className="card-body">
				<div className="d-flex justify-content-between">
					<h6 className="card-title">{ this.props.title } <Loader show={ this.props.loading } /></h6>
					<div className="d-flex align-items-center">
						<nav className="me-2" aria-label="Page navigation example">
							<ul className="pagination pagination-sm justify-content-center m-0">
								<li className={ "page-item m-0" + (!this.hasPrev() ? ' disabled' : '') }>
									<a className="page-link" href="#" tabIndex="-1" aria-disabled="true" onClick={ this.handlePrev }>Prev</a>
								</li>
								<li className={ "page-item m-0" + (!this.hasNext() ? ' disabled' : '') }>
									<a className="page-link" href="#" onClick={ this.handleNext }>Next</a>
								</li>
							</ul>
						</nav>
						{ this.renderPaginationSummary() }
					</div>
				</div>

				<table className="table table-striped">
					<thead>
						<tr>
							{ this.props.columns.map( column =>
								<th scope="col">{ column.name }</th>
							)}
						</tr>
					</thead>
					<tbody>
					{ this.props.data.map( (row, index) =>
						<tr key={ index }>
							{ this.props.columns.map( (column, columnIndex) =>
								<td>{ this.props.cellRenderer(columnIndex, row) }</td>
							)}
						</tr>
					)}
					</tbody>
				</table>
			</div>
		</div>
	}
}

StatsTable.defaultProps = {
	loading: false,
	onOffsetChange: () => null
}

StatsTable.propTypes = {
	title: PropTypes.string.isRequired,
	loading: PropTypes.bool.isRequired,
	className: PropTypes.string,
	columns: PropTypes.array.isRequired,
	data: PropTypes.array.isRequired,
	cellRenderer: PropTypes.func.isRequired,
	total: PropTypes.number,
	offset: PropTypes.number,
	limit: PropTypes.number,
	onOffsetChange: PropTypes.func.isRequired
};

export default StatsTable;