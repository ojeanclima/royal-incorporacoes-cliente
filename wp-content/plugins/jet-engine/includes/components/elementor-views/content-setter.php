<?php
namespace Jet_Engine\Elementor_Views;

class Content_Setter {

	public function __construct() {
		add_action(
			'jet-engine/listing/set-content/elementor',
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
		update_post_meta( $listing_id, '_elementor_data', wp_json_encode( $data ) );
	}

	/**
	 * Convert builder-agnostic data into a elementor format
	 *
	 * @param  array  $raw_data [description]
	 * @return [type]           [description]
	 */
	public function convert_data( $raw_data = [] ) {

		$converted_data = [
			[
				'id'       => $this->get_id(),
				'elType'   => 'container',
				'settings' => [],
				'elements' => [],
				'isInner'  => false,
			],
		];

		foreach ( $raw_data as $el ) {

			// Convert bool 'true' to 'yes'
			foreach ( $el['settings'] as $key => $value ) {
				if ( $value && is_bool( $value ) ) {
					$el['settings'][ $key ] = 'yes';
				}
			}

			$converted_data[0]['elements'][] = [
				'id'         => $this->get_id(),
				'elType'     => 'widget',
				'settings'   => $el['settings'],
				'elements'   => [],
				'widgetType' => $el['type'],
			];
		}

		return $converted_data;
	}

	/**
	 * Return random ID for new elements
	 *
	 * @return [type] [description]
	 */
	public function get_id() {
		return dechex( rand() );
	}

}
