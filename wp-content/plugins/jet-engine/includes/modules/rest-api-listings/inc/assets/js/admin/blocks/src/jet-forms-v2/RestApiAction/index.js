import { __ } from '@wordpress/i18n';
import BearerTokenControls from './BearerTokenControls';
import RapidAPIAuthControls from './RapidAPIAuthControls';
import CustomHeaderControls from './CustomHeaderControls';
import { addFilter } from '@wordpress/hooks';
import EditRestApiRequestAction from './Edit';
import ApplicationPasswordControls from './ApplicationPasswordControls';

addFilter(
	'jet.engine.restapi.authorization.fields.rapidapi',
	'jet-engine/rapid-api',
	RapidAPIAuthControls,
);

addFilter(
	'jet.engine.restapi.authorization.fields.bearer-token',
	'jet-engine/bearer-token',
	BearerTokenControls,
);

addFilter(
	'jet.engine.restapi.authorization.fields.custom-header',
	'jet-engine/custom-header',
	CustomHeaderControls,
);

addFilter(
	'jet.engine.restapi.authorization.fields.application-password',
	'jet-engine/custom-header',
	ApplicationPasswordControls,
);

export default {
	type: 'rest_api_request',
	label: __( 'REST API Request', 'jet-engine' ),
	category: 'advanced',
	edit: EditRestApiRequestAction,
	icon: <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
		<rect x="0" fill="none" width="20" height="20"/>
		<g>
			<path
				d="M16 13c-1.3 0-2.4.8-2.8 2H9c0-.7-.2-1.3-.5-1.8l7.1-7.3c.3 0 .6.1.9.1C17.9 6 19 4.9 19 3.5S17.9 1 16.5 1 14 2.1 14 3.5c0 .3.1.7.2 1l-7 7.2c-.6-.5-1.4-.7-2.2-.7V6.8C6.2 6.4 7 5.3 7 4c0-1.7-1.3-3-3-3S1 2.3 1 4c0 1.3.8 2.4 2 2.8v4.7c-1.2.7-2 2-2 3.4 0 2.2 1.8 4 4 4 1.5 0 2.8-.8 3.4-2h4.7c.4 1.1 1.5 2 2.8 2 1.6 0 3-1.3 3-3C19 14.3 17.6 13 16 13z"/>
		</g>
	</svg>,
	docHref: 'https://crocoblock.com/knowledge-base/jetengine/jetengine-how-to-add-and-edit-cct-items-remotely-using-rest-api/',
	validators: [
		( { settings } ) => {
			return settings?.url ? false : { property: 'url' };
		},
		( { settings } ) => {
			if ( !settings.authorization ||
				'application-password' !== settings.auth_type
			) {
				return false;
			}

			return settings?.application_pass
			       ? false
			       : { property: 'application_pass' };
		},
		( { settings } ) => {
			if ( !settings.authorization ||
				'bearer-token' !== settings.auth_type
			) {
				return false;
			}

			return settings?.bearer_token
			       ? false
			       : { property: 'bearer_token' };
		},
		( { settings } ) => {
			if ( !settings.authorization ||
				'custom-header' !== settings.auth_type
			) {
				return false;
			}

			return [
				settings?.custom_header_name
				? false
				: { property: 'custom_header_name' },
				settings?.custom_header_value
				? false
				: { property: 'custom_header_value' },
			];
		},
		( { settings } ) => {
			if ( !settings.authorization ||
				'rapidapi' !== settings.auth_type
			) {
				return false;
			}

			return [
				settings?.rapidapi_key
				? false
				: { property: 'rapidapi_key' },
				settings?.rapidapi_host
				? false
				: { property: 'rapidapi_host' },
			];
		},
	],
};