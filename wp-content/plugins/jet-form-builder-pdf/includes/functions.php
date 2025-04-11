<?php

use JFB_PDF\Plugin;
use JFB_PDF\Vendor\Auryn\ConfigException;
use JFB_PDF\Vendor\Auryn\InjectionException;
use JFB_PDF\Vendor\Auryn\Injector;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

/**
 * @throws InjectionException|ConfigException
 */
function jet_fb_pdf_setup() {
	/**
	 * We use additional check for a case, when site administrator manually
	 * deletes or deactivates the JetFormBuilder plugin (not via plugin's page)
	 */
	if ( ! function_exists( 'jet_form_builder' ) ||
		! class_exists( '\JFB_Modules\Actions_V2\Module' )
	) {
		return;
	}

	/** @var Plugin $plugin */

	$injector = new Injector();
	$plugin   = new Plugin( $injector );
	$injector->share( $plugin );

	$plugin->setup();

	add_filter(
		'jet-form-builder-pdf/injector',
		function () use ( $injector ) {
			return $injector;
		}
	);

	do_action( 'jet-form-builder-pdf/setup', $injector );
}

function jet_fb_pdf_injector(): Injector {
	return apply_filters( 'jet-form-builder-pdf/injector', false );
}
