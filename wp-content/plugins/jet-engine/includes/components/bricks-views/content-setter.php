<?php
namespace Jet_Engine\Bricks_Views;

class Content_Setter {

	public function __construct() {
		add_action(
			'jet-engine/listing/set-content/bricks',
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
		$data = $this->convert_data( $data );
		update_post_meta( $listing_id, '_bricks_page_content_2', $data );
	}

	/**
	 * Convert builder-agnostic data into a Bricks format
	 *
	 * @param  array  $raw_data [description]
	 * @return [type]           [description]
	 */
	public function convert_data( $raw_data = [] ) {

		$types_map = [
			'jet-listing-dynamic-field' => 'jet-engine-listing-dynamic-field',
			'jet-listing-dynamic-image' => 'jet-engine-listing-dynamic-image',
			'jet-listing-dynamic-link'  => 'jet-engine-listing-dynamic-link',
		];

		$skip_types = [
			'jet-listing-dynamic-repeater',
		];

		$section_id     = $this->get_id();
		$converted_data = [
			[
				'id'       => $section_id,
				'name'     => 'section',
				'parent'   => 0,
				'children' => [],
				'settings' => [],
			]
		];

		foreach ( $raw_data as $el ) {

			if ( in_array( $el['type'], $skip_types ) ) {
				continue;
			}

			$el_id = $this->get_id();
			$converted_data[] = [
				'id'       => $el_id,
				'parent'   => $section_id,
				'settings' => $el['settings'],
				'children' => [],
				'name'     => isset( $types_map[ $el['type'] ] ) ? $types_map[ $el['type'] ] : 'jet-engine-listing-dynamic-field',
			];

			$converted_data[0]['children'][] = $el_id;
		}

		return $converted_data;
	}

	/**
	 * Return random ID for new elements
	 *
	 * @return [type] [description]
	 */
	public function get_id() {
		return class_exists( '\Bricks\Helpers' ) ? \Bricks\Helpers::generate_random_id( false ) : rand( 100000, 999999 );
	}

}
