<?php

namespace JFB_PDF_Modules\Templates;

use JFB_PDF\Plugin;
use JFB_PDF_Modules\Templates\PostType\PostType;
use JFB_PDF_Modules\Components;

class Editor {

	public function init_hooks() {
		add_action(
			'enqueue_block_editor_assets',
			array( $this, 'enqueue_assets' )
		);
	}

	public function enqueue_assets() {
		$screen = get_current_screen();

		if ( PostType::SLUG !== $screen->post_type ) {
			return;
		}

		$script_url   = $this->get_url( 'assets/build/editor.js' );
		$script_asset = require_once $this->get_path( 'assets/build/editor.asset.php' );

		array_push(
			$script_asset['dependencies'],
			Components\Module::HANDLE
		);

		do_action( 'jet-form-builder-pdf/templates-editor/assets' );

		wp_enqueue_script(
			Plugin::SLUG . '-template-editor',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		wp_localize_script(
			Plugin::SLUG . '-template-editor',
			'JetFBTemplateEditorConfig',
			array(
				'imagePlaceholder' => jet_form_builder()->plugin_url( 'assets/img/image-placeholder.jpg' ),
			)
		);
	}

	public function get_url( string $url = '' ): string {
		return JFB_PDF_URL . 'modules/Templates/' . $url;
	}

	public function get_path( string $path = '' ): string {
		return JFB_PDF_PATH . 'modules/Templates/' . $path;
	}


}
