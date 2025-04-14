<?php
namespace Jet_Engine\Timber_Views;

class Content_Setter {

	public function __construct() {
		add_action(
			'jet-engine/listing/set-content/twig',
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
		update_post_meta( $listing_id, '_jet_engine_listing_html', $data );
	}

	/**
	 * Convert builder-agnostic data into a elementor format
	 *
	 * @param  array  $raw_data [description]
	 * @return [type]           [description]
	 */
	public function convert_data( $raw_data = [] ) {

		$converted_data = [];
		$skip_types     = [ 'jet-listing-dynamic-repeater' ];
		$types_map      = [
			'jet-listing-dynamic-field' => 'dynamic-field',
			'jet-listing-dynamic-image' => 'dynamic-image',
			'jet-listing-dynamic-link'  => 'dynamic-link',
		];

		foreach ( $raw_data as $el ) {

			if ( in_array( $el['type'], $skip_types ) ) {
				continue;
			}

			$format = '<div>%s</div>';

			switch ( $el['type'] ) {
				case 'jet-listing-dynamic-field':
					$el_tag = 'jet_engine_data';
					$settings = $el['settings'];
					break;

				case 'jet-listing-dynamic-image':

					if ( ! empty( $el['settings']['is_cct_media'] ) ) {
						$el_tag = 'jet_engine_data';
						$settings = [
							'dynamic_field_source'      => 'object',
							'dynamic_field_post_object' => $el['settings']['dynamic_image_source'],
							'dynamic_field_filter'      => true,
							'filter_callback'           => 'wp_get_attachment_image',
							'attachment_image_size'     => 'thumbnail',
						];
					} else {
						$el_tag = 'jet_engine_url';
						$format = '<div><img src="%s" alt=""></div>';
						$settings = $el['settings'];

						if ( ! empty( $settings['custom_source'] ) ) {
							$settings['dynamic_image_source'] = '_img_by_id';
						} elseif ( 'post_thumbnail' === $settings['dynamic_image_source'] ) {
							$settings['dynamic_image_source'] = '_thumbnail_url';
						}
					}

					break;

				case 'jet-listing-dynamic-link':
					$el_tag = 'jet_engine_url';
					$format = '<div><a href="%s">Read More</a></div>';
					$settings = [];
					break;
			}

			$settings_str = '';
			$settings_map = [
				'dynamic_field_source' => 'source',
				'dynamic_image_source' => 'source',
				'dynamic_field_post_object' => 'key',
				'dynamic_field_wp_excerpt' => 'wp_excerpt',
				'dynamic_excerpt_more' => 'excerpt_more',
				'dynamic_excerpt_length' => 'excerpt_length',
				'dynamic_field_post_meta' => 'meta_key',
				'dynamic_field_option' => 'option_name',
				'dynamic_field_var_name' => 'var_name',
				'dynamic_field_post_meta_custom' => 'custom_key',
				'field_fallback' => 'fallback',
				'object_context' => 'context',
				'size' => 'size',
				'dynamic_image_size' => 'size',
				'custom_source' => 'custom_source',
			];

			$filter_callback = '';

			if ( ! empty( $settings ) ) {

				$settings_arr  = [];
				$unmapped_args = [];

				foreach ( $settings as $key => $value) {
					if ( isset( $settings_map[ $key ] ) ) {
						$settings_arr[] = sprintf( "%s:'%s'", $settings_map[ $key ], $value );
					} else {
						$unmapped_args[ $key ] = $value;
					}
				}

				if (
					! empty( $unmapped_args['dynamic_field_filter'] )
					&& ! empty( $unmapped_args['filter_callback'] )
				) {
					$filter_callback = '|jet_engine_callback(args={%s})';
					$cb_args         = [];

					$unmapped_args['cb'] = $unmapped_args['filter_callback'];

					unset( $unmapped_args['filter_callback'] );
					unset( $unmapped_args['dynamic_field_filter'] );

					foreach ( array_reverse( $unmapped_args ) as $key => $value ) {
						$cb_args[] = sprintf( "%s:'%s'", $key, $value );
					}

					$filter_callback = sprintf( $filter_callback, implode( ',', $cb_args ) );

				}

				$settings_str = implode( ',', $settings_arr );
			}

			$tag = sprintf(
				'{{ %1$s(args={%2$s})%3$s }}',
				$el_tag,
				$settings_str,
				$filter_callback
			);

			$converted_data[] = sprintf( $format, $tag );
		}

		return implode( "\n", $converted_data );
	}
}
