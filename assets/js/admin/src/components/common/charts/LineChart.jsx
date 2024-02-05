import React from "react";
import PropTypes from 'prop-types';
import moment from 'moment';
import { ResponsiveLine } from '@nivo/line';
import { getNumberTickValues } from 'utils/charts';

class LineChart extends React.Component {

	render() {
		const yMax = Math.max( ...this.props.data.map( serie => serie.data.map( record => record.y ) ).flat() );
		const yTickValues = getNumberTickValues(yMax);
		const series = this.props.data.reduce( (prev, cur) => ({...prev, [cur.id]: cur }), {});

		return <ResponsiveLine
			data={ this.props.data }
			curve="monotoneX"
			margin={{ top: 10, right: 30, bottom: 30, left: 30 }}
			xScale={{ type: 'time', format: '%Y-%m-%d' }}
			yScale={{
				type: 'linear',
				min: 0,
				max: yTickValues[yTickValues.length - 1],
				stacked: false,
				reverse: false
			}}
			enableGridX={ false }
			gridYValues={ yTickValues }
			enableArea={ this.props.enableArea } // background below the lines
			xFormat="time:%Y-%m-%d"
			yFormat=" >-.0d"
			tickInterval={ 100 }
			axisBottom={{
				format: '%b %d',
			    legend: 'Day',
			    legendOffset: 40,
			    legendPosition: 'middle',
				useUTC: false,
				precision: 'day',
				tickValues: 5 //series1.data.length <= 8 ? 'every day' : 'every day'
			}}
			axisLeft={{
				tickSize: 5,
				tickPadding: 5,
				tickRotation: 0,
				tickValues: yTickValues
			}}
			colors={{ scheme: 'category10' }}
			pointSize={10}
			lineWidth={4}
			pointLabelYOffset={-12}
			useMesh={true}
			tooltip={({point}) => (
	            <div
	                style={{
	                    padding: 12,
		                display: 'flex',
		                background: '#ffffff',
		                borderRadius: 5,
		                border: '1px solid #92b7d5',
		                alignItems: 'center'
	                }}
	            >{ point.data.yFormatted } { point.data.y !== 1 ? series[point.serieId].plural : series[point.serieId].single}<br /> { moment(point.data.x).format('MMM D') }</div>
	        )}
		/>
	}

}

LineChart.defaultProps = {
	enableArea: true
}

LineChart.propTypes = {
	data: PropTypes.array.isRequired,
	enableArea: PropTypes.bool.isRequired,
};

export default LineChart;