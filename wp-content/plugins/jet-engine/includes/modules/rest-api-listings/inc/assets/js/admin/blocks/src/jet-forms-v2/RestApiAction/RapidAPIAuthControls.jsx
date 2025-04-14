import { ValidatedTextControl } from 'jet-form-builder-actions';
import { WideLine } from 'jet-form-builder-components';
import { __ } from '@wordpress/i18n';

function RapidAPIAuthControls( empty, {
	settings,
	onChangeSettingObj,
} ) {
	return <>
		<WideLine/>
		<ValidatedTextControl
			label={ __( 'RapidAPI Key', 'jet-engine' ) }
			help={ __(
				'X-RapidAPI-Key from endpoint settings at the rapidapi.com',
				'jet-engine',
			) }
			value={ settings.rapidapi_key }
			onChange={ val => onChangeSettingObj( { rapidapi_key: val } ) }
			isErrorSupported={
				( { property } ) => 'rapidapi_key' === property
			}
			required
			__next40pxDefaultSize
			__nextHasNoMarginBottom
		/>
		<WideLine/>
		<ValidatedTextControl
			label={ __( 'RapidAPI Host', 'jet-engine' ) }
			help={ __(
				'X-RapidAPI-Host from endpoint settings at the rapidapi.com',
				'jet-engine',
			) }
			value={ settings.rapidapi_host }
			onChange={ val => onChangeSettingObj( { rapidapi_host: val } ) }
			isErrorSupported={
				( { property } ) => 'rapidapi_host' === property
			}
			required
			__next40pxDefaultSize
			__nextHasNoMarginBottom
		/>
	</>;
}

export default RapidAPIAuthControls;