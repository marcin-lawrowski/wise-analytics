import React from "react";
import PropTypes from 'prop-types';
import { connect } from "react-redux";
import { requestReport, clearReport } from "actions/reports";
import moment from 'moment';
import StatsTable from 'common/data/StatsTable';

class ReferralsTable extends React.Component {

	constructor(props) {
		super(props);

		this.state = {
			offset: 0,
			category: 'Referral'
		}
	}

	componentDidMount() {
		this.refresh();
	}

	componentWillUnmount() {
		this.props.clearReport('sources');
	}

	componentDidUpdate(prevProps, prevState, snapshot) {
		if ((prevProps.startDate !== this.props.startDate || prevProps.endDate !== this.props.endDate) && this.props.startDate && this.props.endDate) {
			this.refresh();
		}
	}

	refresh() {
		this.props.requestReport({
			name: 'sources',
			filters: {
				startDate: moment(this.props.startDate).format('YYYY-MM-DD'),
				endDate: moment(this.props.endDate).format('YYYY-MM-DD'),
				category: 'Referral',
			},
			offset: this.state.offset
		});
	}

	render() {
		return <StatsTable
			title={ `Referral Sources` }
			loading={ this.props.loading }
			columns={[
				{ 'name': 'Source' },
				{ 'name': 'Avg. Visit' },
				{ 'name': 'Total Visits' },
				{ 'name': 'Avg. Events per Visit' }
			]}
			data={ this.props.report.sources }
			cellRenderer={ (columnIndex, visitor) => {
				switch (columnIndex) {
					case 0:
						return visitor.sourceGroup;
					case 1:
						return visitor.avgSessionTime;
					case 2:
						return visitor.totalSessions;
					case 3:
						return visitor.eventsPerSession;
				}
			}}
			offset={ this.props.report.offset }
			limit={ this.props.report.limit }
			total={ this.props.report.total }
			onOffsetChange={ offset => this.setState({ offset: offset }, this.refresh) }
		/>
	}

}

ReferralsTable.propTypes = {
	configuration: PropTypes.object.isRequired,
	startDate: PropTypes.object,
	endDate: PropTypes.object
};

export default connect(
	(state) => ({
		configuration: state.configuration,
		loading: state.reports['sources'].inProgress,
		report: state.reports['sources'].result
	}), { requestReport, clearReport }
)(ReferralsTable);