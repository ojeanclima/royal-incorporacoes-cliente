import { __ } from '@wordpress/i18n';
import { Flex, ToggleControl } from '@wordpress/components';
import {
	WideLine,
	ClearBaseControlStyle,
} from 'jet-form-builder-components';
import {
	ValidatedSelectControl,
} from 'jet-form-builder-actions';
import URLRow from './URLRow';
import BodyRow from './BodyRow';
import { applyFilters } from '@wordpress/hooks';

function EditRestApiRequestAction( props ) {

	const {
		      source,
		      settings,
		      onChangeSettingObj,
	      } = props;

	return <Flex direction="column">
		<URLRow { ...props }/>
		<WideLine/>
		<BodyRow { ...props }/>
		<WideLine/>
		<ToggleControl
			className={ ClearBaseControlStyle }
			label={ __(
				'Authorization',
				'jet-engine',
			) }
			checked={ settings.authorization }
			onChange={ authorization => (
				onChangeSettingObj( { authorization } )
			) }
			__nextHasNoMarginBottom
		/>
		{ settings.authorization && <>
			<WideLine/>
			<ValidatedSelectControl
				label={ __( 'Authorization type', 'jet-engine' ) }
				value={ settings.auth_type }
				onChange={ val => onChangeSettingObj( { auth_type: val } ) }
				options={ [
					{ value: '', label: '--' },
					...source.auth_types,
				] }
			/>
			{ applyFilters(
				`jet.engine.restapi.authorization.fields.${ settings.auth_type }`,
				<></>, props,
			) }
		</> }
	</Flex>;
}

export default EditRestApiRequestAction;