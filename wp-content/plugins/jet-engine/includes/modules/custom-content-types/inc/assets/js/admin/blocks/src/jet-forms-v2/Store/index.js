import { createReduxStore } from '@wordpress/data';
import reducer from './reducer';
import * as actions from './actions';
import * as selectors from './selectors';
import * as resolvers from './resolvers';

export const STORE_NAME = 'jet-engine/cct';

const store = createReduxStore(
	STORE_NAME,
	{
		reducer,
		actions,
		selectors,
		resolvers,
	},
);

export default store;
