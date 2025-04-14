<?php

namespace Jet_Engine\Bricks_Views\Dynamic_Data;

use Jet_Engine\Modules\Custom_Content_Types\Module;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Provider_Jet_Engine_Data extends \Bricks\Integrations\Dynamic_Data\Providers\Base {
	public function register_tags() {
		$name = 'je_current_object_field';

		$tag = [
			'name'     => '{' . $name . '}',
			'label'    => 'Current Object Field - add key after :',
			'group'    => 'Jet Engine Dynamic Data',
			'field'    => 'text',
			'provider' => $this->name
		];

		$this->tags[ $name ] = $tag;
	}

	public function get_tag_value( $tag, $post, $args, $context ) {
		$post    = jet_engine()->listings->data->get_current_object();
		$post_id = $post->ID ?? $post->_ID ?? '';

		// STEP: Check for filter args
		$filters = $this->get_filters_from_args( $args );

		$key = ! empty( $filters['meta_key'] ) ? $filters['meta_key'] : 'meta';

		$value = jet_engine()->listings->data->get_prop(
			$key,
			jet_engine()->listings->data->get_object_by_context()
		);

		// STEP: Apply context (text, link, image, media)
		$value = $this->format_value_for_context( $value, $tag, $post_id, $filters, $context );

		return $value;
	}
}
