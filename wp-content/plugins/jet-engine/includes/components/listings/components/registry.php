<?php
namespace Jet_Engine\Listings\Components;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define components registry class
 */
class Registry {

	private $components = null;

	/**
	 * Constructor for the class
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_components' ] );
	}

	/**
	 * Regsiter all created components as separate instances for appropriate views
	 * @return [type] [description]
	 */
	public function register_components() {

		$components = $this->get_components();

		if ( ! empty( $components ) ) {

			foreach ( $components as $component ) {
				if ( 'publish' === $component->get_status() ) {
					do_action( 'jet-engine/listings/components/register-component-elements', $component );
				}
			}

			// register integrations
			require jet_engine()->listings->components->path( 'integrations.php' );
			new Integrations();

		}

	}

	/**
	 * Get omponents from the DB
	 * 
	 * @return [type] [description]
	 */
	public function get_components() {

		if ( null === $this->components ) {

			$components = get_posts( [
				'post_type'      => jet_engine()->listings->post_type->slug(),
				'posts_per_page' => -1,
				'post_status'    => [ 'publish', 'draft', 'pending' ],
				'meta_query'     => [
					[
						'key'   => '_entry_type',
						'value' => 'component',
					]
				],
			] );

			$this->components = [];

			require_once jet_engine()->listings->components->path( 'component.php' );

			if ( ! empty( $components ) ) {
				foreach ( $components as $component_post ) {
					$component = new Component( $component_post );
					$this->components[ $component->get_element_name() ] = $component;
				}
			}

		}

		return $this->components;
	}

	/**
	 * Returns component instance by name
	 * 
	 * @param  [type] $component_name [description]
	 * @return [type]                 [description]
	 */
	public function get( $component_name, $by = 'name' ) {

		if ( 'id' === $by ) {
			$component_name = jet_engine()->listings->components->get_component_base_name() . '-' . $component_name;
		}

		return isset( $this->get_components()[ $component_name ] ) ? $this->get_components()[ $component_name ] : false;
	}

}
