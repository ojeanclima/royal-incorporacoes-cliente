import {
	LabelWithActions, Label,
	RowControl,
	RowControlEnd,
	Help
} from 'jet-form-builder-components';
import { __ } from '@wordpress/i18n';
import { ExternalLink, TextareaControl } from '@wordpress/components';
const {
	      MacrosFields,
      } = JetFBComponents;

function BodyRow( { settings, onChangeSettingObj } ) {
	return <RowControl>
		{ ( { id } ) => <>
			<LabelWithActions>
				<Label htmlFor={ id }>
					{ __( 'Custom Body', 'jet-engine' ) }
				</Label>
				<MacrosFields
					onClick={ name => onChangeSettingObj( {
						body: (
							settings.body ?? ''
						) + name,
					} ) }
					withCurrent
				/>
			</LabelWithActions>
			<RowControlEnd>
				<TextareaControl
					id={ id }
					value={ settings.body }
					onChange={ body => onChangeSettingObj( { body } ) }
					onBlur={ () => setShowError( true ) }
					__next40pxDefaultSize
					__nextHasNoMarginBottom
				/>
				<Help>
					{ __(
						'By default API request will use all form data as body. Here you can set custom body of your API request in the JSON format.',
						'jet-engine',
					) }
					<br/>
					<ExternalLink
						href="https://www.w3dnetwork.com/json-formatter.html">
						{ __( 'Online editing tool', 'jet-engine' ) }
					</ExternalLink>
					{ ' - ' }
					{ __(
						'switch to the',
						'jet-engine',
					) }
					{ ' ' }
					<b>
						<i>{ __( 'Tree View', 'jet-engine' ) }</i>
					</b>
					{ ', ' }
					{ __(
						'edit object as you need, than switch to',
						'jet-engine',
					) }
					{ ' ' }
					<b>
						<i>{ __( 'Plain Text', 'jet-engine' ) }</i>
					</b>
					{ ' ' }
					{ __(
						'and copy/paste result here.',
						'jet-engine',
					) }
					<br/>
					{ __(
						'You can use the same macros as for the URL.',
						'jet-engine',
					) }
				</Help>
			</RowControlEnd>
		</> }
	</RowControl>;
}

export default BodyRow;