/* eslint-disable import/no-extraneous-dependencies */
import {
	RequiredLabel,
	RowControl,
	RowControlEnd,
	IconText,
} from 'jet-form-builder-components';
import { __ } from '@wordpress/i18n';
import { SelectControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { STORE_NAME } from '../Store';
import { useActionValidatorProvider } from 'jet-form-builder-actions';

// eslint-disable-next-line max-lines-per-function
function ContentTypeRow( { settings, onChangeSettingObj, source } ) {
	const { hasError, setShowError } = useActionValidatorProvider( {
		isSupported: error => 'type' === error?.property,
	} );

	const { error } = useSelect(
		select => (
			{
				error: select( STORE_NAME ).getResolutionError(
					'getType',
					[ settings.type ],
				),
			}
		),
		[ settings.type ],
	);

	return <RowControl>
		{ ( { id } ) => <>
			<RequiredLabel htmlFor={ id }>
				{ __( 'Content Type', 'jet-engine' ) }
			</RequiredLabel>
			<RowControlEnd
				hasError={ hasError || Boolean( error ) }
				showDefaultNotice={ hasError }
			>
				{ Boolean( error ) && <IconText>
					{ error?.message ?? __(
						'Fetching content type was failed',
						'jet-engine',
					) }
				</IconText> }
				<SelectControl
					id={ id }
					value={ settings.type }
					onChange={ type => onChangeSettingObj( { type } ) }
					options={ [
						{ value: '', label: '--' },
						...source.types,
					] }
					onBlur={ () => setShowError( true ) }
					__next40pxDefaultSize
					__nextHasNoMarginBottom
				/>
			</RowControlEnd>
		</> }
	</RowControl>;
}

export default ContentTypeRow;
