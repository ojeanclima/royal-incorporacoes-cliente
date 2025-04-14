<?php
namespace Jet_Engine\Blocks_Views;

class Content_Setter {

	public function __construct() {
		add_action(
			'jet-engine/listing/set-content/blocks',
			[ $this, 'set_content' ],
			10, 2
		);
	}

	/**
	 * Set given builder-agnostic data as content for listing.
	 *
	 * @param [type] $data       Data to set in builder-agnostic format
	 * @param [type] $listing_id [description]
	 */
	public function set_content( $data = [], $listing_id = 0 ) {

		$data      = $this->convert_data( $data );
		$post_data = [
			'ID'           => $listing_id,
			'post_content' => $data,
		];

		wp_update_post( $post_data );
	}

	/**
	 * Convert builder-agnostic data into a elementor format
	 *
	 * @param  array  $raw_data [description]
	 * @return [type]           [description]
	 */
	public function convert_data( $raw_data = [] ) {

		$converted_data = [];
		$types_map      = [
			'jet-listing-dynamic-field'    => 'dynamic-field',
			'jet-listing-dynamic-image'    => 'dynamic-image',
			'jet-listing-dynamic-link'     => 'dynamic-link',
			'jet-listing-dynamic-repeater' => 'dynamic-repeater',
		];

		foreach ( $raw_data as $el ) {
			$converted_data[] = sprintf(
				'<!-- wp:jet-engine/%1$s %2$s /-->',
				isset( $types_map[ $el['type'] ] ) ? $types_map[ $el['type'] ] : 'dynamic-field',
				wp_json_encode( $el['settings'] )
			);
		}

		return implode( "\n", $converted_data );
	}
}
