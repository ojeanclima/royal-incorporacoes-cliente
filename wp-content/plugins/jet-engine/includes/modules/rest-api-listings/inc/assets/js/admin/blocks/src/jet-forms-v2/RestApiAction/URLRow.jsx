import {
	LabelWithActions, RequiredLabel,
	RowControl,
	RowControlEnd,
} from 'jet-form-builder-components';
import { __ } from '@wordpress/i18n';
import { TextareaControl } from '@wordpress/components';
import { useActionValidatorProvider } from 'jet-form-builder-actions';

const {
	      MacrosFields,
      } = JetFBComponents;

function URLRow( { settings, onChangeSettingObj } ) {
	const { hasError, setShowError } = useActionValidatorProvider( {
		isSupported: error => 'url' === error?.property,
	} );

	return <RowControl>
		{ ( { id } ) => <>
			<LabelWithActions>
				<RequiredLabel htmlFor={ id }>
					{ __( 'REST API URL', 'jet-engine' ) }
				</RequiredLabel>
				<MacrosFields
					onClick={ name => onChangeSettingObj( {
						url: (
							settings.url ?? ''
						) + name,
					} ) }
					withCurrent
				/>
			</LabelWithActions>
			<RowControlEnd hasError={ hasError }>
				<TextareaControl
					id={ id }
					value={ settings.url }
					onChange={ url => onChangeSettingObj( { url } ) }
					help={ __(
						'You can use these macros as dynamic part of the URL: %field_name%',
						'jet-engine'
					) }
					onBlur={ () => setShowError( true ) }
					__next40pxDefaultSize
					__nextHasNoMarginBottom
				/>
			</RowControlEnd>
		</> }
	</RowControl>;
}

export default URLRow;