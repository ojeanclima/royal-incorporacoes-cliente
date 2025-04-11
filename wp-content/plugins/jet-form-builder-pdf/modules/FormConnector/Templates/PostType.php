<?php

namespace JFB_PDF_Modules\FormConnector\Templates;

use JFB_PDF_Modules\Templates;

class PostType {

	const META = 'jfb_form';

	public function init_hooks() {
		add_action( 'init', array( $this, 'register_meta' ) );
	}

	public function register_meta() {
		register_post_meta(
			Templates\PostType\PostType::SLUG,
			self::META,
			array(
				'type'          => 'string',
				'show_in_rest'  => true,
				'single'        => true,
				'default'       => '{}',
				'auth_callback' => function ( $res, $key, $post_id, $user_id ) {
					return user_can( $user_id, 'edit_post', $post_id );
				},
			)
		);
	}

}
