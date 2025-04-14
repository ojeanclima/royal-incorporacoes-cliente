import { __ } from '@wordpress/i18n';
import { Flex } from '@wordpress/components';
import {
	WideLine,
} from 'jet-form-builder-components';
import {
	ValidatedSelectControl,
} from 'jet-form-builder-actions';
import { useFields } from 'jet-form-builder-blocks-to-actions';

function EditConnectRelationAction( props ) {

	const {
		      source,
		      settings,
		      onChangeSettingObj,
	      } = props;

	const formFields = useFields( {
		withInner: false,
		placeholder: '--',
	} );

	return <Flex direction="column">
		<ValidatedSelectControl
			label={ __( 'Relation', 'jet-engine' ) }
			labelPosition="side"
			value={ settings.relation }
			onChange={ relation => onChangeSettingObj( { relation } ) }
			options={ [
				{ value: '', label: '--' },
				...source.relations,
			] }
			required
			isErrorSupported={
				( { property } ) => 'relation' === property
			}
		/>
		<WideLine/>
		<ValidatedSelectControl
			label={ __( 'Parent Item ID', 'jet-engine' ) }
			value={ settings.parent_id }
			onChange={ parent_id => onChangeSettingObj( { parent_id } ) }
			options={ formFields }
			required
			isErrorSupported={
				( { property } ) => 'parent_id' === property
			}
		/>
		<WideLine/>
		<ValidatedSelectControl
			label={ __( 'Child Item ID', 'jet-engine' ) }
			value={ settings.child_id }
			onChange={ child_id => onChangeSettingObj( { child_id } ) }
			options={ formFields }
			required
			isErrorSupported={
				( { property } ) => 'child_id' === property
			}
		/>
		<WideLine/>
		<ValidatedSelectControl
			label={ __( 'Update Context', 'jet-engine' ) }
			value={ settings.context }
			onChange={ context => onChangeSettingObj( { context } ) }
			options={ [
				{ value: '', label: '--' },
				...source.context_options,
			] }
		/>
		<WideLine/>
		<ValidatedSelectControl
			label={ __( 'How to Store New Items', 'jet-engine' ) }
			value={ settings.store_items_type }
			onChange={ store_items_type => onChangeSettingObj( {
				store_items_type,
			} ) }
			options={ [
				{ value: '', label: '--' },
				...source.store_items_type_options,
			] }
		/>
	</Flex>;
}

export default EditConnectRelationAction;