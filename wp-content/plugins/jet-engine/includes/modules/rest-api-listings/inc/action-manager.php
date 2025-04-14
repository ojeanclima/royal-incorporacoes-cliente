<?php


namespace Jet_Engine\Modules\Rest_API_Listings;

class Action_Manager {

	public function __construct() {
		if ( $this->can_init() ) {
			add_action(
				'jet-form-builder/actions/register',
				array( $this, 'register_actions' )
			);

			add_action(
				'jet-form-builder/editor-assets/before',
				array( $this, 'editor_assets' )
			);
		}
	}

	public function register_actions( $manager ) {
		require_once Module::instance()->module_path( 'jet-action.php' );

		$manager->register_action_type( new Jet_Action() );
	}

	public function editor_assets() {
		$script_name  = class_exists( '\JFB_Modules\Actions_V2\Module' ) ? 'jet-forms-v2' : 'jet-forms';
		$script_asset = require_once Module::instance()->module_path(
			"assets/js/admin/blocks/build/{$script_name}.asset.php"
		);

		wp_enqueue_script(
			Module::instance()->slug . '-jet-form-action',
			Module::instance()->module_url( "assets/js/admin/blocks/build/{$script_name}.js" ),
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
	}

	public function can_init() {
		return function_exists( 'jet_form_builder' )
				&& version_compare( jet_form_builder()->get_version(), '1.2.3', '>=' );
	}

}
