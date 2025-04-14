import action from './RelationAction';
import { registerAction } from 'jet-form-builder-actions';
import { addFilter } from '@wordpress/hooks';

registerAction( action );

addFilter(
	'jet.fb.preset.editor.custom.condition',
	'jet-engine/connect-relation-items',
	function ( isVisible, customCondition, state ) {
		if ( 'relation_query_var' === customCondition ) {

			return (
				'connect_relation_items' === state.from
				&&
				[ 'query_var', 'object_var' ].includes( state.rel_object_from )
			);
		}
		return isVisible;
	},
);