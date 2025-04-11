<?php

namespace JFB_PDF_Modules\FormConnector\Templates;

use JFB_PDF\Plugin;
use JFB_PDF_Modules\Components;

class Editor {

	public function init_hooks() {
		add_action(
			'jet-form-builder-pdf/templates-editor/assets',
			array( $this, 'templates_editor_assets' )
		);
	}

	public function templates_editor_assets() {
		$script_url   = $this->get_url( 'assets/build/editor.js' );
		$script_asset = require_once $this->get_path( 'assets/build/editor.asset.php' );

		array_push(
			$script_asset['dependencies'],
			Components\Module::HANDLE
		);

		wp_enqueue_script(
			Plugin::SLUG . '-jfb-connector-plugin',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
	}

	public function get_url( string $url = '' ): string {
		return JFB_PDF_URL . 'modules/FormConnector/' . $url;
	}

	public function get_path( string $path = '' ): string {
		return JFB_PDF_PATH . 'modules/FormConnector/' . $path;
	}

}
