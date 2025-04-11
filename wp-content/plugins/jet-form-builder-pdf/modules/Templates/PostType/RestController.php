<?php


namespace JFB_PDF_Modules\Templates\PostType;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

/**
 * We limit viewing of templates because it may contain sensitive information,
 * or personal data of the site administrator
 *
 * Class RestController
 *
 * @package JFB_PDF_Modules\Templates
 */
class RestController extends \WP_REST_Posts_Controller {

	public function get_items_permissions_check( $request ) {
		/**
		 * We give instructions that only those who have access to edit them can view forms
		 */
		$request->set_param( 'context', 'edit' );

		return parent::get_items_permissions_check( $request );
	}

	public function get_item_permissions_check( $request ) {
		/**
		 * We give instructions that only those who have access to edit them can view forms
		 */
		$request->set_param( 'context', 'edit' );

		return parent::get_item_permissions_check( $request );
	}

}
