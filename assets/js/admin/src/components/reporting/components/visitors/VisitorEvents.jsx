import React from "react";
import PropTypes from 'prop-types';
import { connect } from "react-redux";
import { requestReport, clearReport } from "actions/reports";
import moment from 'moment';
import StatsTable from "common/data/StatsTable";

class VisitorEvents extends React.Component {

	constructor(props) {
		super(props);

		this.state = {
			offset: 0
		}
	}

	componentDidMount() {
		this.refresh();
	}

	componentWillUnmount() {
		this.props.clearReport('events');
	}

	componentDidUpdate(prevProps, prevState, snapshot) {
		if ((prevProps.startDate !== this.props.startDate || prevProps.endDate !== this.props.endDate) && this.props.startDate && this.props.endDate) {
			this.setState({ offset: 0 }, this.refresh);
		}
	}

	refresh() {
		this.props.requestReport({
			name: 'events',
			filters: {
				startDate: moment(this.props.startDate).format('YYYY-MM-DD'),
				endDate: moment(this.props.endDate).format('YYYY-MM-DD'),
				visitorId: this.props.id
			},
			offset: this.state.offset
		});
	}

	renderVisitor(event) {
		let name = [event.visitorFirstName, event.visitorLastName].join(' ').trim();
		if (!name) {
			name = 'Visitor #' + event.visitorId;
		}

		return name;
	}

	render() {
		return <StatsTable
			title="Recent Activity"
			loading={ this.props.loading }
			columns={[
				{ 'name': 'Event' },
				{ 'name': 'URI' },
				{ 'name': 'Date' }
			]}
			data={ this.props.report.events }
			cellRenderer={ (columnIndex, row) => {
				switch (columnIndex) {
					case 0:
						return row.typeName ? row.typeName : 'Unknown';
					case 1:
						return <a href={ this.props.configuration.baseUrl + row.uri } target="_blank">{ row.title ? row.title : row.uri }</a>;
					case 2:
						return row.created;
				}
			}}
			offset={ this.props.report.offset }
			limit={ this.props.report.limit }
			total={ this.props.report.total }
			onOffsetChange={ offset => this.setState({ offset: offset }, this.refresh) }
		/>
	}

}

VisitorEvents.propTypes = {
	configuration: PropTypes.object.isRequired,
	id: PropTypes.number.isRequired,
	startDate: PropTypes.object,
	endDate: PropTypes.object
};

export default connect(
	(state) => ({
		configuration: state.configuration,
		loading: state.reports['events'].inProgress,
		report: state.reports['events'].result
	}), { requestReport, clearReport }
)(VisitorEvents);