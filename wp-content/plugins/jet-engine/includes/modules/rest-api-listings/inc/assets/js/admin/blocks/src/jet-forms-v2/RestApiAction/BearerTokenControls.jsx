import { ValidatedTextControl } from 'jet-form-builder-actions';
import { WideLine } from 'jet-form-builder-components';
import { __ } from '@wordpress/i18n';

function BearerTokenControls( empty, {
	settings,
	onChangeSettingObj,
} ) {
	return <>
		<WideLine/>
		<ValidatedTextControl
			label={ __( 'Bearer token', 'jet-engine' ) }
			help={ __(
				'Set token for Bearer Authorization type',
				'jet-engine',
			) }
			value={ settings.bearer_token }
			onChange={ val => onChangeSettingObj( { bearer_token: val } ) }
			isErrorSupported={
				( { property } ) => 'bearer_token' === property
			}
			required
			__next40pxDefaultSize
			__nextHasNoMarginBottom
		/>
	</>;
}

export default BearerTokenControls;