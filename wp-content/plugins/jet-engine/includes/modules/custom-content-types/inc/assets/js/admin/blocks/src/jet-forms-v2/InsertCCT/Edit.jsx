import { __ } from '@wordpress/i18n';
import { Flex } from '@wordpress/components';
import {
	WideLine,
} from 'jet-form-builder-components';
import {
	ValidatedSelectControl,
} from 'jet-form-builder-actions';
import ContentTypeRow from './ContentTypeRow';
import FieldsMapRow from './FieldsMapRow';
import DefaultFieldsMapRow from './DefaultFieldsMapRow';
import { useSelect } from '@wordpress/data';
import { STORE_NAME } from '@/jet-forms-v2/Store';

function EditCustomContentType( props ) {

	const {
		      source,
		      settings,
		      onChangeSettingObj,
	      } = props;

	const { error, cctType } = useSelect(
		select => (
			{
				error: select( STORE_NAME ).getResolutionError(
					'getType',
					[ settings.type ],
				),
				cctType: select( STORE_NAME ).getType( settings.type ),
			}
		),
		[ settings.type ],
	);

	return <Flex direction="column">
		<ContentTypeRow { ...props }/>
		<WideLine/>
		<ValidatedSelectControl
			label={ __( 'Item Status', 'jet-engine' ) }
			value={ settings.status }
			onChange={ status => onChangeSettingObj( { status } ) }
			options={ [
				{ value: '', label: '--' },
				...source.statuses,
			] }
		/>
		{ cctType?.fields?.length && !Boolean( error ) && <>
			<WideLine/>
			<FieldsMapRow { ...props }/>
			<WideLine/>
			<DefaultFieldsMapRow { ...props } />
		</> }
	</Flex>;
}

export default EditCustomContentType;