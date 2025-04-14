<?php
namespace Jet_Smart_Filters\Endpoints;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Posts class
 */
class Plugin_Settings extends Base {
	/**
	 * Returns route name
	 */
	public function get_name() {

		return 'plugin-settings';
	}

	public function get_args() {

		return array(
			'key'      => array(
				'default'  => false,
				'required' => false,
			),
			'settings' => array(
				'required' => true,
			),
		);
	}

	public function callback( $request ) {

		$args     = $request->get_params();
		$key      = $args['key'];
		$settings = $args['settings'];

		// Sanitize the incoming settings using a dedicated method
		$sanitized_settings = $this->sanitize_settings( $settings );

		if ( $key ) {

			// update specified option by key
			$data = array_map(
				function( $setting ) {
					return is_array( $setting ) ? $setting : esc_attr( $setting );
				},
				$sanitized_settings
			);

			jet_smart_filters()->settings->update( $key, $data );

			if ( $key === 'seo_sitemap_rules' ) {
				jet_smart_filters()->seo->sitemap->update();
			}

		} else {

			// update all settings
			$data = array_map(
				function( $setting ) {
					return is_array( $setting ) ? $setting : esc_attr( $setting );
				},
				$sanitized_settings
			);

			jet_smart_filters()->seo->sitemap->process_settings( $data );

			update_option( jet_smart_filters()->settings->key, $data );

		}

		return rest_ensure_response( [
			'status'  => 'success',
			'message' => __( 'Settings have been saved', 'jet-smart-filters' ),
		] );
	}

	/**
	 * Sanitize plugin settings based on predefined types.
	 *
	 * This method iterates through the settings array, applies the correct
	 * sanitization or type conversion for each key, and returns the sanitized array.
	 *
	 * @param array $settings The settings array to sanitize.
	 * @return array The sanitized settings.
	 */
	private function sanitize_settings( $settings ) {
		// Initialize an array to store sanitized settings
		$sanitized_settings = [];

		// Iterate through the settings
		foreach ( $settings as $key => $value ) {
			// Replace 'false' (string) with an empty string for specific keys
			if ( $key === 'provider_preloader_fixed_position' && $value === 'false' ) {
				$sanitized_settings[ $key ] = '';
			} else {
				// Leave other values unchanged
				$sanitized_settings[ $key ] = $value;
			}
		}

		// Return the array of sanitized settings
		return $sanitized_settings;
	}

	/**
	 * Check user access to current end-popint
	 */
	public function permission_callback( $request ) {
		return current_user_can( 'manage_options' );
	}
}
