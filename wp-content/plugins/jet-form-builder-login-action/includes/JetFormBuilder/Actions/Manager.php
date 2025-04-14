<?php


namespace Jet_FB_Login\JetFormBuilder\Actions;

use Jet_FB_Login\Plugin;
use JetLoginCore\JetFormBuilder\ActionsManager;

class Manager extends ActionsManager {

	public function register_controller( \Jet_Form_Builder\Actions\Manager $manager ) {
		$manager->register_action_type( new Action() );
		$manager->register_action_type( new ResetAction() );
	}

	/**
	 * @return void
	 */
	public function before_init_editor_assets() {
		$script_asset = require_once Plugin::instance()->plugin_dir( 'assets/js/login.editor.asset.php' );

		wp_enqueue_script(
			Plugin::instance()->slug,
			Plugin::instance()->plugin_url( 'assets/js/login.editor.js' ),
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		$script_asset = require_once Plugin::instance()->plugin_dir( 'assets/js/reset.editor.asset.php' );

		wp_enqueue_script(
			Plugin::instance()->slug . '-reset',
			Plugin::instance()->plugin_url( 'assets/js/reset.editor.js' ),
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		$script_asset = require_once Plugin::instance()->plugin_dir( 'assets/js/patterns.asset.php' );

		wp_enqueue_script(
			Plugin::instance()->slug . '-pattern',
			Plugin::instance()->plugin_url( 'assets/js/patterns.js' ),
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
	}


	/**
	 * Supported only >= 3.4.0 JetFormBuilder
	 *
	 * @return bool
	 */
	public function can_init(): bool {
		return class_exists( '\JFB_Modules\Actions_V2\Module' );
	}

	public function on_base_need_update() {
		$this->add_admin_notice(
			'warning',
			__(
				'<b>Warning</b>: <b>JetFormBuilder Login Action</b> needs <b>JetFormBuilder</b> update.',
				'jet-form-builder-login-action'
			)
		);
	}

	public function on_base_need_install() {
	}
}
