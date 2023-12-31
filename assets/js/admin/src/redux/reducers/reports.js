import { handleServerActions } from "utils/actions";

const defaultServerActions = {
	'overview.highlights': { result: { users: 0, pageViews: 0, avgPagesPerVisit: 0.0, avgSessionTime: '0s' } },
	'pages.top': { result: { pages: [] } },
	'visitors.last': { result: { visitors: [] } },
	'visitors.daily': { result: { visitors: [] } },
	'events': { result: { events: [] } }
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