import { __ } from '@wordpress/i18n';
import EditConnectRelationAction from './Edit';
import { connection } from '@wordpress/icons';

export default {
	type: 'connect_relation_items',
	label: __( 'Connect Relation Items', 'jet-engine' ),
	category: 'content',
	edit: EditConnectRelationAction,
	icon: connection,
	docHref: 'https://crocoblock.com/knowledge-base/jetengine/jetengine-how-to-connect-relation-items-using-forms/',
	validators: [
		( { settings } ) => {
			return settings?.relation ? false : { property: 'relation' };
		},
		( { settings } ) => {
			return settings?.parent_id ? false : { property: 'parent_id' };
		},
		( { settings } ) => {
			return settings?.child_id ? false : { property: 'child_id' };
		},
	],
};