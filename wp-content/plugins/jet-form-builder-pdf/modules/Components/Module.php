<?php

namespace JFB_PDF_Modules\Components;

class Module {

	const HANDLE = 'crocoblock-components';

	public function __construct() {
		add_action(
			'enqueue_block_editor_assets',
			array( $this, 'enqueue_assets' ),
			0
		);
	}

	public function enqueue_assets() {
		$script_url   = $this->get_url( 'assets/build/index.js' );
		$script_asset = require_once $this->get_path( 'assets/build/index.asset.php' );

		wp_register_script(
			self::HANDLE,
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
	}

	private function get_url( string $url = '' ): string {
		return JFB_PDF_URL . 'modules/Components/' . $url;
	}

	private function get_path( string $path = '' ): string {
		return JFB_PDF_PATH . 'modules/Components/' . $path;
	}

}
