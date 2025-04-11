<?php

namespace JFB_PDF_Modules\WooCommerce;

final class Module {

	public function __construct() {
		add_action( 'jet-form-builder-pdf/render-body-bottom', array( $this, 'dequeue_styles' ) );
	}

	public function dequeue_styles() {
		wp_deregister_style( 'common' );
	}

}
