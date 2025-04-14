import { __ } from '@wordpress/i18n';
import { STORE_NAME } from '../Store';
import {
	Label,
	RowControl,
	RowControlEnd,
} from 'jet-form-builder-components';
import { useSelect } from '@wordpress/data';
import { FieldsMapField } from 'jet-form-builder-actions';
import { useFields } from 'jet-form-builder-blocks-to-actions';

function FieldsMapRow( { getMapField, setMapField, settings } ) {

	const formFields = useFields( { withInner: false } );

	const { isResolving, cctType } = useSelect(
		select => (
			{
				isResolving: select( STORE_NAME ).isResolving(
					'getType',
					[ settings.type ],
				),
				cctType: select( STORE_NAME ).getType( settings.type ),
			}
		),
		[ settings.type ],
	);

	return <RowControl align="flex-start">
		<Label>{ __( 'Fields map', 'jet-engine' ) }</Label>
		<RowControlEnd
			gap={ 4 }
			style={ { opacity: isResolving ? '0.5' : '1' } }
		>
			{ formFields.map( ( field ) => <FieldsMapField
				key={ field.value }
				tag={ field.value }
				label={ field.label }
				isRequired={ field.required }
				formFields={ [
					{ value: '', label: '--' },
					...cctType.fields
				] }
				value={ getMapField( { name: field.value } ) }
				onChange={ value => setMapField(
					{ nameField: field.value, value },
				) }
			/> ) }
		</RowControlEnd>
	</RowControl>;
}

export default FieldsMapRow;
