import React from "react";
import PropTypes from 'prop-types';
import { connect } from "react-redux";
import { requestReport } from "actions/reports";
import moment from 'moment';
import { ResponsivePie } from '@nivo/pie'
import Loader from "common/Loader";

class SourcesChart extends React.Component {

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
			name: 'sessions.sourceCategories',
			filters: {
				startDate: moment(this.props.startDate).format('YYYY-MM-DD'),
				endDate: moment(this.props.endDate).format('YYYY-MM-DD')
			}
		});
	}

	render() {
		const data = this.props.report.sourceCategories.map( (record, index) => ({ "id": record.source, "value": record.totalVisitors }) );

		return <div className="card">
			<div className="card-body p-0">
				<h6 className="card-title text-muted">Sources <Loader show={ this.props.loading } /></h6>

				<div style={ { height: 220 }}>
					<ResponsivePie
				        data={data}
				        sortByValue={true}
				        margin={{ top: 40, right: 80, bottom: 40, left: 80 }}
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
				        tooltip={({datum}) => (
				            <div
				                style={{
				                    padding: 12,
					                display: 'flex',
					                background: '#ffffff',
					                borderRadius: 5,
					                border: '1px solid #92b7d5',
					                alignItems: 'center'
				                }}
				            ><strong>{ datum.label }</strong>: { datum.value } { datum.value > 1 ? 'Visitors' : 'Visitor' }</div>
				        )}
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

SourcesChart.propTypes = {
	configuration: PropTypes.object.isRequired,
	startDate: PropTypes.object,
	endDate: PropTypes.object
};

export default connect(
	(state) => ({
		configuration: state.configuration,
		loading: state.reports['sessions.sourceCategories'].inProgress,
		report: state.reports['sessions.sourceCategories'].result
	}), { requestReport }
)(SourcesChart);