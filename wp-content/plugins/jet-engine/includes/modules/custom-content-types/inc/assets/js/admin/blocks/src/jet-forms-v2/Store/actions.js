import { SET_TYPES } from './constants';

export function saveType( cctObject ) {
	return ( { select, dispatch } ) => {
		const types = select.getTypes();

		dispatch( {
			type: SET_TYPES,
			payload: {
				...types,
				[ cctObject.id ]: { ...cctObject },
			},
		} );
	};
}