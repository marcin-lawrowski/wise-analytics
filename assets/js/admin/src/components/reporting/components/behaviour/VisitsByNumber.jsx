import React from "react";
import PropTypes from 'prop-types';
import { connect } from "react-redux";
import { requestReport, clearReport } from "actions/reports";
import moment from 'moment';
import StatsTable from "common/data/StatsTable";
import { Link } from "react-router-dom";

class VisitsByNumber extends React.Component {

	constructor(props) {
		super(props);

		this.state = {
			offset: 0
		}
	}

	componentDidMount() {
		this.refresh();
	}

	componentDidUpdate(prevProps, prevState, snapshot) {
		if ((prevProps.startDate !== this.props.startDate || prevProps.endDate !== this.props.endDate) && this.props.startDate && this.props.endDate) {
			this.setState({ offset: 0 }, this.refresh);
		}
	}

	componentWillUnmount() {
		this.props.clearReport('behaviour.visits.by.number');
	}

	refresh() {
		this.props.requestReport({
			name: 'behaviour.visits.by.number',
			filters: {
				startDate: moment(this.props.startDate).format('YYYY-MM-DD'),
				endDate: moment(this.props.endDate).format('YYYY-MM-DD')
			},
			offset: this.state.offset,
			sortColumn: this.state.sortColumn,
			sortDirection: this.state.sortDirection
		});
	}

	renderVisitor(visitor) {
		let name = [visitor.firstName, visitor.lastName].join(' ').trim();
		if (!name) {
			name = 'Visitor #' + visitor.id;
		}

		return <Link to={ '/visitors/browse/visitor/' + visitor.id } title="Go to details">{ name }</Link>;
	}

	render() {
		return <StatsTable
			title={ this.props.title }
			loading={ this.props.loading }
			columns={[
				{ 'name': 'Visit Number', sortable: 'userTotalVisits' },
				{ 'name': 'Visits', sortable: 'userTotalVisitsNumber' },
				{ 'name': 'Avg. Visit', sortable: 'avgSessionTime' },
				{ 'name': '% of Visits', sortable: 'percentageOfTotal' },
			]}
			data={ this.props.report.visits }
			rowRenderer={ record => [
				{ value: record.userTotalVisits },
				{ value: record.userTotalVisitsNumber },
				{ value: record.avgSessionTime },
				{ value: record.percentageOfTotal ? record.percentageOfTotal + ' %' : null }
			]}
		/>
	}
}

VisitsByNumber.defaultProps = {
	title: 'Visits by Visit Number'
}

VisitsByNumber.propTypes = {
	configuration: PropTypes.object.isRequired,
	startDate: PropTypes.object,
	endDate: PropTypes.object,
	title: PropTypes.string
};

export default connect(
	(state) => ({
		configuration: state.configuration,
		loading: state.reports['behaviour.visits.by.number'].inProgress,
		report: state.reports['behaviour.visits.by.number'].result
	}), { requestReport, clearReport }
)(VisitsByNumber);