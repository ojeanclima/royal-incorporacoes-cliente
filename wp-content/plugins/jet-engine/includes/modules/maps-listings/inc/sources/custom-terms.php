<?php
namespace Jet_Engine\Modules\Maps_Listings\Source;

class Custom_Terms extends Terms {

	/**
	 * Returns source ID
	 *
	 * @return string
	 */
	public function get_id() {
		return 'custom-terms';
	}

	/**
	 * Defines if is source is for preloading non-JetEngine fields
	 * @return boolean [description]
	 */
	public function is_custom() {
		return true;
	}

	public function add_preload_hooks( $preload_fields ) {

		foreach ( $preload_fields as $field ) {

			$field  = str_replace( '_custom::terms::', '', $field );
			$fields = explode( '+', $field );

			if ( 1 === count( $fields ) ) {
				add_filter( 'update_term_metadata', function( $return, $term_id, $meta_key, $meta_value ) use ( $field ) {

					if ( $field === $meta_key ) {
						$this->preload( $term_id, $meta_value, $meta_key );
					}

					return $return;
				}, 10, 4 );

				add_action( 'add_term_metadata', function( $return, $term_id, $meta_key, $meta_value ) use ( $field ) {

					if ( $field === $meta_key ) {
						$this->preload( $term_id, $meta_value, $meta_key );
					}

					return $return;
				}, 10, 4 );

			} else {
				$this->field_groups[] = $fields;
			}
		}

		if ( ! empty( $this->field_groups ) ) {
			add_action( 'saved_term', array( $this, 'preload_groups' ), 9999 );
		}

	}

	public function filtered_preload_fields( $field ) {
		return false !== strpos( $field, '::terms::' );
	}

}
