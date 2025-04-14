import { ValidatedTextControl } from 'jet-form-builder-actions';
import { WideLine } from 'jet-form-builder-components';
import { __ } from '@wordpress/i18n';

function ApplicationPasswordControls( empty, {
	settings,
	onChangeSettingObj,
} ) {
	return <>
		<WideLine/>
		<ValidatedTextControl
			label={ __( 'User:password string', 'jet-engine' ) }
			help={ __(
				'Set application user and password separated with `:`',
				'jet-engine',
			) }
			value={ settings.application_pass }
			onChange={ val => onChangeSettingObj( { application_pass: val } ) }
			isErrorSupported={
				( { property } ) => 'application_pass' === property
			}
			required
			__next40pxDefaultSize
			__nextHasNoMarginBottom
		/>
	</>;
}

export default ApplicationPasswordControls;