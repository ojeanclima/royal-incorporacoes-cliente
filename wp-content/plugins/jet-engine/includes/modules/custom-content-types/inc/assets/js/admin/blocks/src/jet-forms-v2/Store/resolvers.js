import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

export const getType = ( type ) => async ( { dispatch } ) => {
	if ( !type ) {
		return;
	}
	const path = addQueryArgs(
		window.JetEngineCCT.fetch_path,
		{ type },
	);

	const { success, notices, fields } = await apiFetch( { path } );

	if ( !success ) {
		throw new Error( notices?.[ 0 ]?.message )
	}

	const preparedFields = [];

	for ( const field of fields ) {
		if ( '_ID' === field.value ) {
			field.label += ' (will update the item)';
		}
		preparedFields.push( { ...field } );
	}

	dispatch.saveType( {
		id: type,
		fields: preparedFields,
	} );
};