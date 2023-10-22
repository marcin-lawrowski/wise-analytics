import moment from 'moment';

export function getDatesRange(preset) {
	if (preset === 'today') {
		return { startDate: moment().toDate(), endDate: moment().toDate() };
	}
	if (preset === 'tomorrow') {
		return { startDate: moment().add(1, 'days').toDate(), endDate: moment().add(1, 'days').toDate() };
	}
	if (preset === 'yesterday') {
		return { startDate: moment().subtract(1, 'days').toDate(), endDate: moment().subtract(1, 'days').toDate() };
	}
	if (preset === 'thisWeek') {
		return { startDate: moment().startOf('week').toDate(), endDate: moment().toDate() };
	}
	if (preset === 'last7Days') {
		return { startDate: moment().subtract(6, 'days').toDate(), endDate: moment().toDate() };
	}
	if (preset === 'next7Days') {
		return { startDate: moment().toDate(), endDate: moment().add(6, 'days').toDate() };
	}
	if (preset === 'lastWeek') {
		return { startDate: moment().subtract(1, 'weeks').startOf('week').toDate(), endDate: moment().subtract(1, 'weeks').endOf('week').toDate() };
	}
	if (preset === 'last14Days') {
		return { startDate: moment().subtract(13, 'days').toDate(), endDate: moment().toDate() };
	}
	if (preset === 'thisMonth') {
		return { startDate: moment().startOf('month').toDate(), endDate: moment().endOf('month').toDate() };
	}
	if (preset === 'nextMonth') {
		return { startDate: moment().add(1, 'months').startOf('month').toDate(), endDate: moment().add(1, 'months').endOf('month').toDate() };
	}
	if (preset === 'last30Days') {
		return { startDate: moment().subtract(29, 'days').toDate(), endDate: moment().toDate() };
	}
	if (preset === 'next30Days') {
		return { startDate: moment().toDate(), endDate: moment().add(29, 'days').toDate() };
	}
	if (preset === 'lastMonth') {
		return { startDate: moment().subtract(1, 'months').startOf('month').toDate(), endDate: moment().subtract(1, 'months').endOf('month').toDate() };
	}
}