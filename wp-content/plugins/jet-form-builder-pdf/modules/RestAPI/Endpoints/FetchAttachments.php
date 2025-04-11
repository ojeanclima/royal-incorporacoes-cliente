<?php

namespace JFB_PDF_Modules\RestAPI\Endpoints;

use Jet_Form_Builder\Admin\Exceptions\Empty_Box_Exception;
use Jet_Form_Builder\Db_Queries\Views\View_Base;
use JFB_Components\Rest_Api\Rest_Api_Endpoint_Base;
use JFB_Components\Rest_Api\Traits\Paginated_Args;
use JFB_Components\Rest_Api\Dynamic_Rest_Url_Trait;
use JFB_PDF_Modules\FormRecord\Admin\MetaBoxes\GenerateFileBox;

class FetchAttachments extends Rest_Api_Endpoint_Base {

	use Paginated_Args;
	use Dynamic_Rest_Url_Trait;

	public static function get_rest_base() {
		return 'attachments/box/(?P<record_id>[\d]+)';
	}

	public function get_common_args(): array {
		return array(
			'record_id' => array(
				'type'     => 'integer',
				'required' => true,
			),
		);
	}

	public static function get_methods() {
		return \WP_REST_Server::READABLE;
	}

	public function check_permission(): bool {
		return current_user_can( 'manage_options' );
	}

	public function run_callback( \WP_REST_Request $request ) {
		$box  = ( new GenerateFileBox() )->set_id( $request->get_param( 'record_id' ) );
		$args = View_Base::get_paginated_args( $this->get_paginate_args( $request ) );

		try {
			$records = $box->get_raw_list( $args );
		} catch ( Empty_Box_Exception $exception ) {
			return new \WP_REST_Response(
				array(
					'message'  => __( 'Attachments not found', 'jet-form-builder-pdf' ),
					'is_empty' => true,
				),
				404
			);
		}

		return new \WP_REST_Response(
			array(
				'list'  => $box->prepare_list( $records ),
				'total' => $box->get_total(),
			)
		);
	}

}
