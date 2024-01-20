import React from "react";
import PropTypes from 'prop-types';
import { connect } from "react-redux";
import { requestReport } from "actions/reports";
import moment from 'moment';
import { ResponsivePie } from '@nivo/pie'
import Loader from "common/Loader";

class LanguagesChart extends React.Component {

	componentDidMount() {
		this.refresh();
	}

	componentDidUpdate(prevProps, prevState, snapshot) {
		if ((prevProps.startDate !== this.props.startDate || prevProps.endDate !== this.props.endDate) && this.props.startDate && this.props.endDate) {
			this.refresh();
		}
	}

	refresh() {
		this.props.requestReport({
			name: 'visitors.languages',
			filters: {
				startDate: moment(this.props.startDate).format('YYYY-MM-DD'),
				endDate: moment(this.props.endDate).format('YYYY-MM-DD')
			}
		});
	}

	render() {
		const data = this.props.report.languages.map( (record, index) => ({ "id": record.language ?? '(not set)', "value": record.totalVisitors }) );

		return <div className="card">
			<div className="card-body p-0">
				<h6 className="card-title text-muted">Languages <Loader show={ this.props.loading } /></h6>

				<div style={ { height: 300 }}>
					<ResponsivePie
				        data={data}
				        sortByValue={true}
				        margin={{ top: 20, right: 50, bottom: 50, left: 50 }}
				        innerRadius={0}
				        padAngle={0.7}
				        cornerRadius={3}
				        colors={{ scheme: 'pastel2' }}
				        activeOuterRadiusOffset={8}
				        borderWidth={1}
				        borderColor={{
				            from: 'color',
				            modifiers: [
				                [
				                    'darker',
				                    0.2
				                ]
				            ]
				        }}
				        arcLinkLabelsSkipAngle={10}
				        arcLinkLabelsDiagonalLength={4}
				        arcLinkLabelsStraightLength={10}
				        arcLinkLabelsTextColor="#333333"
				        arcLinkLabelsThickness={2}
				        arcLinkLabelsColor={{ from: 'color' }}
				        arcLabelsSkipAngle={10}
				        arcLabelsTextColor={{
				            from: 'color',
				            modifiers: [
				                [
				                    'darker',
				                    2
				                ]
				            ]
				        }}
				    />
				</div>
			</div>
		</div>
	}
}

LanguagesChart.propTypes = {
	configuration: PropTypes.object.isRequired,
	startDate: PropTypes.object,
	endDate: PropTypes.object
};

export default connect(
	(state) => ({
		configuration: state.configuration,
		loading: state.reports['visitors.languages'].inProgress,
		report: state.reports['visitors.languages'].result
	}), { requestReport }
)(LanguagesChart);