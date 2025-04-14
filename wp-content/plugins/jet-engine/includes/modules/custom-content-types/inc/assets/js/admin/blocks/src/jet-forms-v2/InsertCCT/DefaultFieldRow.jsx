/* eslint-disable import/no-extraneous-dependencies */
import {
	Label,
	RowControl,
} from 'jet-form-builder-components';
import { Card, Flex, TextControl } from '@wordpress/components';

function DefaultFieldRow( {
	label,
	value,
	onChange,
} ) {
	return <Card elevation={ 2 }>
		<Flex
			direction="column"
			gap={ 3 }
			style={ { padding: '1em' } }
		>
			<RowControl controlSize={ 1 }>
				{ ( { id } ) => <>
					<Label htmlFor={ id }>
						{ label }
					</Label>
					<TextControl
						id={ id }
						value={ value }
						onChange={ onChange }
						__next40pxDefaultSize
						__nextHasNoMarginBottom
					/>
				</> }
			</RowControl>
		</Flex>
	</Card>;
}

export default DefaultFieldRow;