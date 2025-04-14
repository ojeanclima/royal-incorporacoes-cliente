<?php
namespace Jet_Engine\Modules\Maps_Listings\Source;

class Custom_Users extends Users {

	/**
	 * Returns source ID
	 *
	 * @return string
	 */
	public function get_id() {
		return 'custom-users';
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

			$field  = str_replace( '_custom::users::', '', $field );
			$fields = explode( '+', $field );

			if ( 1 === count( $fields ) ) {
				add_filter( 'update_user_metadata', function( $return, $user_id, $meta_key, $meta_value ) use ( $field ) {

					if ( $field === $meta_key ) {
						$this->preload( $user_id, $meta_value, $meta_key );
					}

					return $return;
				}, 10, 4 );

				add_action( 'add_user_metadata', function( $return, $user_id, $meta_key, $meta_value ) use ( $field ) {

					if ( $field === $meta_key ) {
						$this->preload( $user_id, $meta_value, $meta_key );
					}

					return $return;
				}, 10, 4 );

			} else {
				$this->field_groups[] = $fields;
			}
		}

		if ( ! empty( $this->field_groups ) ) {
			add_action( 'wp_update_user', array( $this, 'preload_groups' ), 9999 );
			add_action( 'jet-engine/maps-listing/preload/force/custom-users', array( $this, 'force_preload_groups' ) );
		}

	}

	public function filtered_preload_fields( $field ) {
		return false !== strpos( $field, '::users::' );
	}

}
