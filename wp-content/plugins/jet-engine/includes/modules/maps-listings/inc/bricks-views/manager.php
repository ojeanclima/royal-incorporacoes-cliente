<?php
namespace Jet_Engine\Modules\Maps_Listings\Bricks_Views;

use Bricks\Helpers;
use Jet_Engine\Modules\Maps_Listings\Preview_Trait;

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Manager {

	use Preview_Trait;

	/**
	 * Elementor Frontend instance
	 *
	 * @var null
	 */
	public $frontend = null;

	/**
	 * Constructor for the class
	 */
	function __construct() {
		add_action( 'jet-engine/bricks-views/init', array( $this, 'init' ), 10 );
		add_action( 'jet-smart-filters/render/ajax/before', array( $this, 'initialize_map_filter_settings' ), 10, 2 );
	}

	public function init() {
		add_action( 'jet-engine/bricks-views/register-elements', array( $this, 'register_elements' ), 11 );

		if ( bricks_is_builder() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'preview_scripts' ) );
		}

		add_action( 'jet-engine/maps-listings/get-map-marker', array( $this, 'setup_bricks_query' ), 10, 3 );
	}

	public function register_elements() {
		\Bricks\Elements::register_element( $this->module_path( 'maps-listings.php' ) );
	}

	public function module_path( $relative_path = '' ) {
		return jet_engine()->plugin_path( 'includes/modules/maps-listings/inc/bricks-views/' . $relative_path );
	}

	public function setup_bricks_query( $listing_id, $page_id, $element_id ) {
		$settings = [];

		if ( $page_id && $element_id ) {
			$settings        = Helpers::get_element_settings( $page_id, $element_id );
			$settings['_id'] = $element_id;
		}

		jet_engine()->bricks_views->listing->render->set_bricks_query( $listing_id, $settings );
	}

	public function initialize_map_filter_settings( $instance, $provider_id ) {
		if ( $provider_id !== 'jet-engine-maps' ) {
			return;
		}

		add_filter( 'jet-engine/maps-listings/marker-data', array( $this, 'sanitize_marker_data' ), 10 );
	}

	public function sanitize_marker_data( $marker ) {
		return wp_unslash( $marker );
	}
}