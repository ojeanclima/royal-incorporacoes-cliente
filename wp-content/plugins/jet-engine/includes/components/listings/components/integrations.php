<?php
namespace Jet_Engine\Listings\Components;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Register components integrations into a different parts of JetEngine
 */
class Integrations {

	public function __construct() {

		add_action( 'jet-engine/register-macros', [ $this, 'register_components_macros' ] );
		add_action( 'jet-engine/listings/document/set-preview', [ $this, 'set_preview_state' ], 10, 2 );
		add_filter( 'jet-engine/dynamic-sources/url-fields', [ $this, 'register_img_source' ], 0, 2 );
		add_filter( 'jet-engine/elementor-view/dynamic-link/generel-options', [ $this, 'register_url_source' ] );
		add_filter( 'jet-engine/listings/data/sources', [ $this, 'add_control_source' ] );
		add_filter( 'jet-engine/listings/dynamic-image/image-data', [ $this, 'get_image_data' ], 0, 2 );
		add_filter( 'jet-engine/listings/dynamic-link/custom-url', [ $this, 'get_link_url' ], 0, 2 );
		add_filter( 'jet-engine/listings/dynamic-image/custom-url', [ $this, 'get_image_url' ], 0, 2 );
		add_filter( 'jet-engine/listings/dynamic-field/custom-value', [ $this, 'get_field_data' ], 0, 2 );

	}

	/**
	 * Register macros to get compnent value
	 * 
	 * @return [type] [description]
	 */
	public function register_components_macros() {
		require jet_engine()->listings->components->path( 'macros/component-control-value.php' );
		new Macros\Component_Control_Value();
	}

	/**
	 * Set preview state on global listing hook
	 * 
	 */
	public function set_preview_state( $document, $preview ) {
		
		if ( ! jet_engine()->listings->components->is_component( $document->get_main_id() ) ) {
			return;
		}

		$component = jet_engine()->listings->components->get( $document->get_main_id(), 'id' );
		jet_engine()->listings->components->state->set( $component->get_default_state() );

	}

	/**
	 * Register component control image source where it allowed
	 * 
	 * @param  [type] $sources [description]
	 * @param  string $for     [description]
	 * @return [type]          [description]
	 */
	public function register_img_source( $sources, $for = '' ) {

		$sources[0]['values'][] = [
			'value' => 'component_control',
			'label' => __( 'Component Control Value', 'jet-engine' ),
		];

		return $sources;
	}

	/**
	 * Register component control value source for Dynamic links
	 * 
	 * @param  [type] $sources [description]
	 * @param  string $for     [description]
	 * @return [type]          [description]
	 */
	public function register_url_source( $sources ) {

		$sources['component_control'] = __( 'Component Control Value', 'jet-engine' );

		return $sources;
	}

	/**
	 * Returns component prop value
	 * 
	 * @param  [type] $result   [description]
	 * @param  [type] $settings [description]
	 * @return [type]           [description]
	 */
	public function get_field_data( $result, $settings ) {

		if ( ! empty( $settings['dynamic_field_source'] 
			&& 'component_control_value' === $settings['dynamic_field_source'] )
			&& ! empty( $settings['dynamic_field_post_meta_custom'] )
		) {
			$result = jet_engine()->listings->components->state->get( $settings['dynamic_field_post_meta_custom'] );
		} else {
			return $result;
		}

		// Return raw result for non-string or non-numeric values
		if ( is_array( $result )
			|| is_object( $result )
			|| is_bool( $result )
			|| is_null( $result )
		) {
			return $result;
		}

		return do_shortcode( wp_kses_post( $result ) );
	}

	/**
	 * Maybe get image link URL from Component control
	 * 
	 * @param  [type] $url      [description]
	 * @param  array  $settings [description]
	 * @return [type]           [description]
	 */
	public function get_image_url( $url, $settings = [] ) {

		if ( ! empty( $settings['image_link_source'] )
			&& 'component_control' === $settings['image_link_source']
			&& ! empty( $settings['image_link_source_custom'] )
		) {
			$url = jet_engine()->listings->components->state->get( $settings['image_link_source_custom'] );
		}

		return $url;
	}

	/**
	 * Maybe get dynamic link URL from Component control
	 * 
	 * @param  [type] $url      [description]
	 * @param  array  $settings [description]
	 * @return [type]           [description]
	 */
	public function get_link_url( $url, $settings = [] ) {

		if ( ! empty( $settings['dynamic_link_source'] )
			&& 'component_control' === $settings['dynamic_link_source']
			&& ! empty( $settings['dynamic_link_source_custom'] )
		) {
			$url = jet_engine()->listings->components->state->get( $settings['dynamic_link_source_custom'] );
		}

		return $url;
	}

	/**
	 * Register source control
	 * 
	 * @param array $sources [description]
	 */
	public function add_control_source( $sources = [] ) {
		$sources['component_control_value'] = esc_html__( 'Component Control Value', 'jet-engine' );
		return $sources;
	}

	/**
	 * Get component control image data
	 * 
	 * @return [type] [description]
	 */
	public function get_image_data( $result, $settings ) {

		if ( ! empty( $settings['dynamic_image_source'] )
			&& 'component_control' === $settings['dynamic_image_source']
			&& ! empty( $settings['dynamic_image_source_custom'] )
		) {
			$result = jet_engine()->listings->components->state->get( $settings['dynamic_image_source_custom'] );
		}

		return $result;
	}

}
