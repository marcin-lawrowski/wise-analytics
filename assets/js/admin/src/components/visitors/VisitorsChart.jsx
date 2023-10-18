import React from "react";
import PropTypes from 'prop-types';
import { connect } from "react-redux";
import { requestReport } from "actions/reports";
import moment from 'moment';
import Spinner from "common/Spinner";
import { ResponsiveLine } from '@nivo/line';

class VisitorsChart extends React.Component {

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
			name: 'visitors.daily',
			filters: {
				startDate: moment(this.props.startDate).format('YYYY-MM-DD'),
				endDate: moment(this.props.endDate).format('YYYY-MM-DD')
			}
		});
	}

	render() {
		if (this.props.report.visitors.length === 0) {
			return null;
		}

		const data = [
			{
				"id": "Visitors",
				"color": "hsl(150, 70%, 50%)",
				"data": this.props.report.visitors.map( (record, index) => ({ "x": record.date, "y": record.visitors }) )
			}
		];

		return <React.Fragment>
			<h5 className="d-flex">Visitors <Spinner show={ this.props.loading } /></h5>

			<div className="card p-1">
				<div className="card-body text-center">
					<div style={ { height: 200 }}>
						<ResponsiveLine
							data={data}
							margin={{ top: 10, right: 110, bottom: 30, left: 30 }}
							xScale={{ type: 'point' }}
							yScale={{
								type: 'linear',
								min: 'auto',
								max: 'auto',
								stacked: true,
								reverse: false
							}}
							yFormat=" >-.2f"
							axisTop={null}
							axisRight={null}
							axisBottom={{
								tickSize: 5,
								tickPadding: 5,
								tickRotation: 0
							}}
							axisLeft={{
								tickSize: 5,
								tickPadding: 5,
								tickRotation: 0
							}}
							pointSize={10}
							pointColor={{ theme: 'background' }}
							pointBorderWidth={2}
							pointBorderColor={{ from: 'serieColor' }}
							pointLabelYOffset={-12}
							useMesh={true}
							legends={[
							{
								anchor: 'bottom-right',
								direction: 'column',
								justify: false,
								translateX: 100,
								translateY: 0,
								itemsSpacing: 0,
								itemDirection: 'left-to-right',
								itemWidth: 80,
								itemHeight: 20,
								itemOpacity: 0.75,
								symbolSize: 12,
								symbolShape: 'circle',
								symbolBorderColor: 'rgba(0, 0, 0, .5)',
								effects: [
								    {
								        on: 'hover',
								        style: {
								            itemBackground: 'rgba(0, 0, 0, .03)',
								            itemOpacity: 1
								        }
								    }
								]
								}
							]}
						/>
					</div>
				</div>
			</div>
		</React.Fragment>
	}
}

VisitorsChart.propTypes = {
	configuration: PropTypes.object.isRequired,
	startDate: PropTypes.object,
	endDate: PropTypes.object
};

export default connect(
	(state) => ({
		configuration: state.configuration,
		loading: state.reports['visitors.daily'].inProgress,
		report: state.reports['visitors.daily'].result
	}), { requestReport }
)(VisitorsChart);