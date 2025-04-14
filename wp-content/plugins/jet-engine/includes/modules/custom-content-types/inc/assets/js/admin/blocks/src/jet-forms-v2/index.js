import action from './InsertCCT';
import { addComputedField, registerAction } from 'jet-form-builder-actions';
import DynamicInsertedCCTID from '../DynamicInsertedCCTID';
import { register } from '@wordpress/data';
import Store from './Store';
import { addFilter } from '@wordpress/hooks';

register( Store );
registerAction( action );
addComputedField( DynamicInsertedCCTID );

addFilter(
	'jet.fb.preset.editor.custom.condition',
	'jet-engine/cct-query-var',
	function ( isVisible, customCondition, state ) {
		if ( 'cct_query_var' === customCondition ) {

			return (
				'custom_content_type' === state.from && 'query_var' ===
				state.post_from
			);
		}
		return isVisible;
	},
);