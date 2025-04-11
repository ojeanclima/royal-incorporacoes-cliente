<?php

namespace JFB_PDF_Modules\RestAPI;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

class Module {

	public function __construct( Controller $controller ) {
		$controller->rest_api_init();
	}
}
