<?php
/**
 * External Layout Switcher module
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Jet_Engine_Module_Layout_Switcher class
 */
class Jet_Engine_Module_Layout_Switcher extends Jet_Engine_External_Module_Base {

	/**
	 * Module ID
	 *
	 * @return string
	 */
	public function module_id() {
		return 'jet-engine-layout-switcher';
	}

	/**
	 * Check if related plugin for current external module is active
	 *
	 * @return boolean [description]
	 */
	public function is_related_plugin_active() {
		return defined( 'JET_ENGINE_LAYOUT_SWITCHER_VERSION' );
	}

	/**
	 * Module name
	 *
	 * @return string
	 */
	public function module_name() {
		return __( 'Layout Switcher', 'jet-engine' );
	}

	/**
	 * Returns detailed information about current module for the dashboard page
	 * @return [type] [description]
	 */
	public function get_module_description() {
		return '<p>It allows users to switch between the Listing Grid layouts on the front end to select the most convenient one.</p>
				<p>This module adds a new Layout Switcher widget.</p>';
	}

	/**
	 * Returns information about the related plugin for current module
	 *
	 * @return [type] [description]
	 */
	public function get_related_plugin_data() {
		return array(
			'file' => 'jet-engine-layout-switcher/jet-engine-layout-switcher.php',
			'name' => 'JetEngine - Layout Switcher',
		);
	}

	/**
	 * Returns array links to the module-related resources
	 * @return array
	 */
	public function get_module_links() {
		return array(
			array(
				'label' => __( 'Layout Switcher Overview', 'jet-engine' ),
				'url'   => 'https://crocoblock.com/knowledge-base/jetengine/layout-switcher-widget-overview/',
			),
		);
	}

	/**
	 * Module init
	 *
	 * @return void
	 */
	public function module_init() {}

}
