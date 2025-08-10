import React from "react";
import PropTypes from 'prop-types';
import moment from 'moment';
import { ResponsiveLine } from '@nivo/line';
import { getNumberTickValues } from 'utils/charts';

class ComparisonLineChart extends React.Component {

	getMargins(chartDataFormatted) {
		let marginLeft = this.props.marginLeft;
		let marginRight = 30;
		if (this.props.data.length > 0) {
			const maxLetters = Math.max( ...chartDataFormatted[0].data.map( record => record.formattedY.length ) );
			if (maxLetters > 0) {
				marginLeft = maxLetters * 10;
			}
		}
		if (this.props.data.length > 1) {
			const maxLetters = Math.max( ...chartDataFormatted[1].data.map( record => record.formattedY.length ) );
			if (maxLetters > 0) {
				marginRight = maxLetters * 10;
			}
		}

		return { top: 10, right: marginRight, bottom: 60, left: marginLeft }
	}

	render() {
		if (this.props.data.length === 0) {
			return null;
		}

		let chartData = this.props.data.map( serie => ({...serie, data: serie.data.map( record => ({ ...record, formattedY: serie.formatter ? serie.formatter(record.y) : record.y }) )}) );
		const maxValues = this.props.data.map( serie => Math.max( ...serie.data.map( record => record.y ) ) );
		const maxValue = Math.max( ...maxValues );
		const ratios = maxValues.map( maxValueInSerie => maxValueInSerie > 0 ? maxValue / maxValueInSerie : 0 );
		const yTickValues = getNumberTickValues(maxValue);
		const series = this.props.data.reduce( (prev, cur) => ({...prev, [cur.id]: cur }), {});
		let axisRight = undefined;
		const axisLeftFormatter = this.props.data[0].formatter ?? ((x) => parseInt(x));

		// data normalisation:
		if (this.props.data.length > 1) {
			const axisRightFormatter = this.props.data[1].formatter ?? ((x) => parseInt(x));
			chartData = chartData.map( (serie, index) => ({...serie, data: serie.data.map( record => ({ ...record, formattedY: serie.formatter ? serie.formatter(record.y) : record.y, y: record.y * ratios[index] }) )}) );
			axisRight = {
				tickSize: 5,
				tickPadding: 5,
				tickRotation: 0,
				tickValues: yTickValues,
				format: y => axisRightFormatter(ratios[1] > 0 ? y / ratios[1] : 0)
			}
		}

		return <ResponsiveLine
			data={ chartData }
			curve="monotoneX"
			margin={ this.getMargins(chartData) }
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
			enableArea={ this.props.enableArea }
			xFormat="time:%Y-%m-%d"
			tickInterval={ 100 }
			axisBottom={{
				format: '%b %d',
			    legend: 'Day',
			    legendOffset: 30,
			    legendPosition: 'middle',
				useUTC: false,
				precision: 'day',
				tickValues: 5
			}}
			axisLeft={{
				tickSize: 5,
				tickPadding: 5,
				tickRotation: 0,
				tickValues: yTickValues,
				format: y => axisLeftFormatter(ratios[0] > 0 ? y / ratios[0] : 0)
			}}
			axisRight={ axisRight }
			colors={{ scheme: 'category10' }}
			pointSize={10}
			lineWidth={4}
			pointLabelYOffset={-12}
			useMesh={true}
			legends={[
				{
					anchor: 'bottom-left',
					direction: 'row',
					justify: false,
					translateX: 0,
					translateY: 60,
					itemsSpacing: 10,
					itemDirection: 'left-to-right',
					itemWidth: 110,
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
	            >{ point.data.formattedY } { point.data.y !== 1 ? series[point.serieId].plural : series[point.serieId].single}<br /> { moment(point.data.x).format('MMM D') }</div>
	        )}
		/>
	}

}

ComparisonLineChart.defaultProps = {
	marginLeft: 30,
	enableArea: true,
	yFormat: " >-.0d"
}

ComparisonLineChart.propTypes = {
	marginLeft: PropTypes.number.isRequired,
	data: PropTypes.array.isRequired,
	enableArea: PropTypes.bool.isRequired
};

export default ComparisonLineChart;