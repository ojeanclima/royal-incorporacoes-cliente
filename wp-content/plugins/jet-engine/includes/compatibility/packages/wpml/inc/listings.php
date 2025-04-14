<?php

namespace Jet_Engine\Compatibility\Packages\Jet_Engine_WPML_Package\Listings;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Manager {

	/**
	 * A reference to an instance of this class.
	 *
	 * @access private
	 * @var    object
	 */
	private static $instance = null;

	/**
	 * A reference to an instance of compatibility package.
	 *
	 * @access private
	 * @var    object
	 */
	private $package = null;

	private function __construct( $package = null ) {
		$this->package = $package;

		add_filter( 'jet-engine/listings/frontend/rendered-listing-id', array( $this->package, 'set_translated_object' ) );
		// Translated media and posts fields.
		add_filter( 'jet-engine/listing/data/get-post-meta', array( $this, 'set_translated_post_meta' ), 10, 3 );
	}

	public function set_translated_post_meta( $value, $key, $post_id ) {

		if ( empty( $value ) ) {
			return $value;
		}

		$post_type = get_post_type( $post_id );

		if ( ! is_post_type_translated( $post_type ) ) {
			return $value;
		}

		$post_type_fields = jet_engine()->meta_boxes->get_meta_fields_for_object( $post_type );

		if ( empty( $post_type_fields ) ) {
			return $value;
		}

		$field_args = null;

		foreach ( $post_type_fields as $field ) {
			if ( ! empty( $field['name'] ) && $key === $field['name'] ) {
				$field_args = $field;
				break;
			}
		}

		if ( empty( $field_args ) ) {
			return $value;
		}

		$supported_field_types = array( 'media', 'posts' );

		if ( empty( $field_args['type'] ) || ! in_array( $field_args['type'], $supported_field_types ) ) {
			return $value;
		}

		$tm_settings = wpml_load_core_tm()->get_settings();

		if ( empty( $tm_settings ) ) {
			return $value;
		}

		if ( ! isset( $tm_settings['custom_fields_translation'] ) || ! isset( $tm_settings['custom_fields_translation'][ $key ] ) ) {
			return $value;
		}

		if ( WPML_IGNORE_CUSTOM_FIELD === $tm_settings['custom_fields_translation'][ $key ] ) {
			return $value;
		}

		switch ( $field_args['type'] ) {

			case 'media':

				if ( is_numeric( $value ) ) {

					$value = apply_filters( 'wpml_object_id', $value, 'attachment', true );

				} elseif ( is_array( $value ) && isset( $value['id'] ) ) {

					$value['id'] = apply_filters( 'wpml_object_id', $value['id'], 'attachment', true );

				} elseif ( is_array( $value ) ) {

					$value = array_map( function( $item ) {

						if ( is_numeric( $item ) ) {

							return apply_filters( 'wpml_object_id', $item, 'attachment', true );

						} elseif ( is_array( $item ) && isset( $item['id'] )  ) {

							$item['id'] = apply_filters( 'wpml_object_id', $item['id'], 'attachment', true );
							return $item;
						}

						return $item;
					}, $value );
				}

				break;

			case 'posts':

				if ( is_array( $value ) ) {

					$value = array_map( function( $item ) {
						return apply_filters( 'wpml_object_id', $item, get_post_type( $item ), true );
					}, $value );

				} else {
					$value = apply_filters( 'wpml_object_id', $value, get_post_type( $value ), true );
				}

				break;
		}

		return $value;
	}

	/**
	 * Returns the instance.
	 *
	 * @access public
	 * @return object
	 */
	public static function instance( $package = null ) {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self( $package );
		}

		return self::$instance;

	}
	
}
