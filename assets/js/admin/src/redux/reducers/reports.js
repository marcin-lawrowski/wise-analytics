import { handleServerActions } from "utils/actions";

const defaultServerActions = {
	'overview.highlights': { result: { visitors: { total: 0, new: 0, returning: 0, percentNew: 0 }, pageViews: { total: 0 }, avgPagesPerVisit: { ratio: 0.0 }, avgSessionTime: { time: '0s' } } },
	'pages.top': { result: { pages: [], total: 0, offset: 0 } },
	'visitors.last': { result: { visitors: [] } },
	'visitors.daily': { result: { visitors: [] } },
	'visitors.languages': { result: { languages: [] } },
	'visitors.devices': { result: { devices: [] } },
	'visitor.information': { result: undefined },
	'sessions.daily': { result: { sessions: [] } },
	'sessions.sources': { result: { sources: [] } },
	'sessions.sourceCategories': { result: { sourceCategories: [] } },
	'pages.views.daily': { result: { pageViews: [] } },
	'events': { result: { events: [], total: 0, offset: 0 } }
}

const defaultState = {
	...defaultServerActions
};

export default function reports(state = defaultState, action) {
	let actionsState = handleServerActions(state, action, defaultServerActions, 'REPORTS');
	if (actionsState) {
		return actionsState;
	}
	
	return state;
}