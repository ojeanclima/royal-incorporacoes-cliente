<?php
/**
 * Timber view class
 */
namespace Jet_Engine\Timber_Views\Components;

use Jet_Engine\Timber_Views\Package;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Register {

	public function __construct() {
		add_action( 'jet-engine/twig-views/editor/before-render-preview', [ $this, 'setup_components_preview' ] );
		add_action( 'jet-engine/twig-views/register-functions', [ $this, 'register_functions' ] );
		add_action( 'jet-engine/twig-views/editor/after-save', [ $this, 'save_props' ], 10, 2 );
		add_action( 'jet-engine/listings/components/update-settings', [ $this, 'on_settings_update' ] );
	}

	/**
	 * Synch elementor controls with component settings
	 * 
	 * @param  [type] $component [description]
	 * @return [type]            [description]
	 */
	public function on_settings_update( $component ) {
		
		if ( 'twig' !== $component->get_render_view() ) {
			return;
		}

		$props         = $component->get_props();
		$styles        = $component->get_styles();
		$page_settings = get_post_meta( $component->get_id(), '_elementor_page_settings', true );
		$listing_data  = get_post_meta( $component->get_id(), '_listing_data', true );

		if ( empty( $page_settings ) ) {
			$page_settings = [];
		}

		if ( empty( $listing_data ) ) {
			$listing_data = [];
		}

		$page_settings['component_controls_list']       = $props;
		$listing_data['component_controls_list']        = $props;
		$page_settings['component_style_controls_list'] = $styles;
		$listing_data['component_style_controls_list']  = $styles;

		update_post_meta( $component->get_id(), '_elementor_page_settings', $page_settings );
		update_post_meta( $component->get_id(), '_listing_data', $listing_data );

	}

	/**
	 * Save component props on editor save
	 * 
	 * @param  [type] $component_id [description]
	 * @param  array  $settings     [description]
	 * @return [type]               [description]
	 */
	public function save_props( $component_id, $settings = [] ) {
		
		if ( ! jet_engine()->listings->components->is_component( $component_id ) ) {
			return;
		}

		$component = jet_engine()->listings->components->get( $component_id, 'id' );

		$controls = ! empty( $settings['component_controls_list'] ) ? $settings['component_controls_list'] : [];
		$styles   = ! empty( $settings['component_style_controls_list'] ) ? $settings['component_style_controls_list'] : [];

		$component->set_props( $controls );
		$component->set_styles( $styles );

	}

	/**
	 * Register component relaed functions
	 * 
	 * @param  [type] $functions_registry [description]
	 * @return [type]                     [description]
	 */
	public function register_functions( $functions_registry ) {
		require_once Package::instance()->package_path( 'components/functions/component-control.php' );
		$functions_registry->register_function( new Functions\Component_Control() );
	}

	/**
	 * Setup state for the components preview
	 * 
	 * @param  [type] $preview_object [description]
	 * @return [type]                 [description]
	 */
	public function setup_components_preview( $preview_object ) {
		
		$listing_id = ! empty( $_POST['id'] ) ? $_POST['id'] : false;

		if ( ! jet_engine()->listings->components->is_component( $listing_id ) ) {
			return;
		}

		$component = jet_engine()->listings->components->get( $listing_id, 'id' );
		$controls  = ! empty( $_POST['settings']['component_controls_list'] ) ? $_POST['settings']['component_controls_list'] : [];

		if ( ! is_array( $controls ) ) {
			$controls = [];
		}

		jet_engine()->listings->components->state->set( $this->get_default_component_props( $component, $controls ) );

	}

	/**
	 * Get default props for given component
	 * 
	 * @param  [type] $component [description]
	 * @param  array  $controls  [description]
	 * @return [type]            [description]
	 */
	public function get_default_component_props( $component, $controls = [] ) {
		
		$props = [];

		foreach ( $controls as $control ) {
			$default_value = $component->get_prop_default( $control );
			$props[ $control['control_name'] ] = $default_value;
		}

		return $props;
	}

}
