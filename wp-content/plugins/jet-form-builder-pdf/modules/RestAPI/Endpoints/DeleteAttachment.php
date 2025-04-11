<?php


namespace JFB_PDF_Modules\RestAPI\Endpoints;

use Jet_Form_Builder\Exceptions\Query_Builder_Exception;
use JFB_Components\Rest_Api\Rest_Api_Endpoint_Base;
use JFB_PDF_Modules\FormRecord\Services\Attachment;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

class DeleteAttachment extends Rest_Api_Endpoint_Base {

	private $service;

	public function __construct( Attachment $service ) {
		$this->service = $service;
	}

	public static function get_rest_base() {
		return 'attachments/delete';
	}

	public static function get_methods() {
		return \WP_REST_Server::DELETABLE;
	}

	public function check_permission(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function run_callback( \WP_REST_Request $request ) {
		$ids = array_map( 'absint', $request['checked'] ?? array() );

		/**
		 * Relative rows also deleting on `delete_attachment` hook
		 *
		 * @see \JFB_PDF_Modules\FormRecord\Module::delete_relative_rows
		 */
		foreach ( $ids as $attachment_id ) {
			wp_delete_attachment( $attachment_id, true );
		}

		return new \WP_REST_Response(
			array(
				'message' => _n(
					'This attachment has been deleted.',
					'These attachments have been deleted.',
					count( $ids ),
					'jet-form-builder-pdf'
				),
			)
		);
	}
}
