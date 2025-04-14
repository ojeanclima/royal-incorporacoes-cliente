import { __ } from '@wordpress/i18n';
import { STORE_NAME } from '../Store';
import {
	Label,
	RowControl,
	RowControlEnd,
} from 'jet-form-builder-components';
import { useSelect } from '@wordpress/data';
import DefaultFieldRow from './DefaultFieldRow';

function DefaultFieldsMapRow( { getMapField, setMapField, settings } ) {

	const { isResolving, cctTypeFields } = useSelect(
		select => {
			const cctType = select( STORE_NAME ).getType( settings.type );

			return {
				isResolving: select( STORE_NAME ).isResolving(
					'getType',
					[ settings.type ],
				),
				cctTypeFields: cctType?.fields?.filter?.(
					( { value } ) => '_ID' !== value,
				) ?? [],
			};
		},
		[ settings.type ],
	);

	return <RowControl align="flex-start">
		<Label>{ __( 'Default Fields', 'jet-engine' ) }</Label>
		<RowControlEnd
			gap={ 4 }
			style={ { opacity: isResolving ? '0.5' : '1' } }
		>
			{ cctTypeFields.map( ( field ) => <DefaultFieldRow
				key={ field.value }
				label={ field.label }
				value={ getMapField( {
					source: 'default_fields',
					name: field.value,
				} ) }
				onChange={ value => setMapField( {
					source: 'default_fields',
					nameField: field.value,
					value,
				} ) }
			/> ) }
		</RowControlEnd>
	</RowControl>;
}

export default DefaultFieldsMapRow;
