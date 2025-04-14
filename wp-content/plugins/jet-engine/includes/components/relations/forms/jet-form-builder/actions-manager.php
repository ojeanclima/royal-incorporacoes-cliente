<?php


namespace Jet_Engine\Relations\Forms\Jet_Form_Builder_Forms;

use Jet_Engine\Relations\Forms;

class Actions_Manager {

	public function __construct() {
		if ( class_exists( '\Jet_Form_Builder\Actions\Manager' ) ) {
			require_once jet_engine()->relations->component_path( 'forms/jet-form-builder/action.php' );

			jet_form_builder()->actions->register_action_type( new Action() );
		}

		add_action(
			'jet-form-builder/editor-assets/before',
			array( $this, 'editor_assets' )
		);

	}

	public function editor_assets() {
		$script_name  = class_exists( '\JFB_Modules\Actions_V2\Module' ) ? 'jfb-action-v2' : 'jfb-action';
		$script_asset = require_once jet_engine()->plugin_path(
			"includes/components/relations/assets/js/{$script_name}.asset.php"
		);

		wp_enqueue_script(
			Forms\Manager::instance()->slug . '-jet-form-action',
			jet_engine()->plugin_url( "includes/components/relations/assets/js/{$script_name}.js" ),
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

	}

}