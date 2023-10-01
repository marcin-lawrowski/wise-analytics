import { combineReducers } from 'redux';
import configuration from './configuration';
import reports from './reports';

const mainReducers = combineReducers({
	configuration, reports
})

export default mainReducers