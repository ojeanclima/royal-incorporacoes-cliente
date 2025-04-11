<?php

namespace JFB_PDF_Modules\RestAPI;

use JFB_Components\Rest_Api\Rest_Api_Controller_Base;
use JFB_Components\Rest_Api\Rest_Api_Endpoint_Base;
use JFB_PDF\Plugin;
use JFB_PDF\Vendor\Auryn\InjectionException;
use JFB_PDF\Vendor\Auryn\Injector;
use JFB_PDF_Modules\RestAPI\Endpoints\DeleteAttachment;
use JFB_PDF_Modules\RestAPI\Endpoints\FetchAttachments;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

class Controller extends Rest_Api_Controller_Base {

	private $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * @return array|Rest_Api_Endpoint_Base[]
	 * @throws InjectionException
	 */
	public function routes(): array {
		return array(
			$this->plugin->get_injector()->make( DeleteAttachment::class ),
			$this->plugin->get_injector()->make( FetchAttachments::class ),
		);
	}
}
