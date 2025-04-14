import { ValidatedTextControl } from 'jet-form-builder-actions';
import { WideLine } from 'jet-form-builder-components';
import { __ } from '@wordpress/i18n';

function CustomHeaderControls( empty, {
	settings,
	onChangeSettingObj,
} ) {
	return <>
		<WideLine/>
		<ValidatedTextControl
			label={ __( 'Header name', 'jet-engine' ) }
			help={ __(
				'Set authorization header name. Could be found in your API docs',
				'jet-engine',
			) }
			value={ settings.custom_header_name }
			onChange={ val => onChangeSettingObj( {
				custom_header_name: val,
			} ) }
			isErrorSupported={
				( { property } ) => 'custom_header_name' === property
			}
			required
			__next40pxDefaultSize
			__nextHasNoMarginBottom
		/>
		<WideLine/>
		<ValidatedTextControl
			label={ __( 'Header value', 'jet-engine' ) }
			help={ __(
				'Set authorization header value. Could be found in your API docs or you user profile related to this API',
				'jet-engine',
			) }
			value={ settings.custom_header_value }
			onChange={ val => onChangeSettingObj( {
				custom_header_value: val,
			} ) }
			isErrorSupported={
				( { property } ) => 'custom_header_value' === property
			}
			required
			__next40pxDefaultSize
			__nextHasNoMarginBottom
		/>
	</>;
}

export default CustomHeaderControls;