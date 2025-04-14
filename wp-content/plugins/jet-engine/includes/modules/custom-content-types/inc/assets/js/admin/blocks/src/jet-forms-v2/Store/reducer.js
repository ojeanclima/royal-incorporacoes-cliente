import {
	SET_TYPES,
} from './constants';
import { combineReducers } from '@wordpress/data';

const api = function ( state = {}, action ) {
	switch ( action?.type ) {
		case SET_TYPES:
			return {
				...state,
				types: action.payload,
			};
	}

	return state;
};

const reducer = combineReducers( {
	api,
} );

export default reducer;