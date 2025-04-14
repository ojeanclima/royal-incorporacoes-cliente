<?php
/**
 * Bricks views manager
 */
namespace Jet_Engine\Bricks_Views;

use Bricks\Database;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Manager class
 */
class Manager {

	/**
	 * Frontend instance
	 *
	 * @var null
	 */
	public $frontend = null;

	/**
	 * Listing manager instance
	 * 
	 * @var null
	 */
	public $listing = null;

	/**
	 * Constructor for the class
	 */
	function __construct() {
		
		if ( ! $this->has_bricks() ) {
			return;
		}

		do_action( 'jet-engine/bricks-views/init' );

		$this->init_components();

		add_action( 'init', array( $this, 'register_elements' ), 10 );
		add_action( 'init', array( $this, 'init_listings' ), 10 );
		add_action( 'init', array( $this, 'integrate_jet_engine_in_bricks_loop' ), 10 );
		add_action( 'init', array( $this, 'register_providers' ), 90 );

		add_filter( 'jet-engine/gallery/grid/args', array( $this, 'add_arguments_to_gallery_grid' ), 10 );
		add_filter( 'jet-engine/gallery/slider/args', array( $this, 'add_arguments_to_gallery_slider' ), 10 );
		add_filter( 'jet-engine/gallery/lightbox-attr', array( $this, 'add_attributes_to_gallery' ), 10, 2 );

		add_filter( 'bricks/elements/jet-engine-listing-dynamic-field/controls', array( $this, 'filter_controls' ), 10, 2 );

		add_filter( 'bricks/builder/i18n', function( $i18n ) {
			$i18n['jetengine'] = esc_html__( 'JetEngine', 'jet-engine' );

			return $i18n;
		} );

		// Add JetEngine icons font
		add_action( 'wp_enqueue_scripts', function() {
			// Enqueue your files on the canvas & frontend, not the builder panel. Otherwise custom CSS might affect builder)
			if ( bricks_is_builder() ) {
				wp_enqueue_style(
					'jet-engine-icons',
					jet_engine()->plugin_url( 'assets/lib/jetengine-icons/icons.css' ),
					array(),
					jet_engine()->get_version()
				);
			}
		} );

		$this->compat_tweaks();
		$this->register_content_setter();
	}

	public function init_export_import_manager() {
		require $this->component_path( 'export-import.php' );
		new Export_Import();
	}

	/**
	 * Register and intialize Content Setter class to liste attempts to set Bricks content to some listing
	 *
	 * @return [type] [description]
	 */
	public function register_content_setter() {
		require $this->component_path( 'content-setter.php' );
		new Content_Setter();
	}

	public function init_listings() {
		require $this->component_path( 'listing/manager.php' );
		$this->listing = new Listing\Manager();
	}

	public function init_components() {

		require $this->component_path( 'components/register.php' );
		new Components\Register();

	}

	public function integrate_jet_engine_in_bricks_loop() {

		require_once $this->component_path( 'query-loop.php' );
		new Query_Loop();

		// TODO: remove jet_smart_filters()->get_version() check later, ! but keep function_exists( 'jet_smart_filters' ) !
		if ( function_exists( 'jet_smart_filters' ) && jet_smart_filters()->get_version() >= '3.5.6' ) {
			require_once $this->component_path( 'filters.php' );
			new Filters();
		}
	}

	public function register_providers() {
		require_once $this->component_path( 'dynamic-data/providers.php' );
		require jet_engine()->bricks_views->component_path( 'dynamic-data/provider-jet-engine-data.php' );
		require jet_engine()->bricks_views->component_path( 'dynamic-data/provider-jet-engine-macros.php' );
		require jet_engine()->bricks_views->component_path( 'dynamic-data/provider-jet-engine-query-builder.php' );

		$dynamic_data_providers = apply_filters( 'jet-engine/bricks-views/dynamic_data/register_providers', array(
			'jet-engine-data'          => '\Jet_Engine\Bricks_Views\Dynamic_Data',
			'jet-engine-macros'        => '\Jet_Engine\Bricks_Views\Dynamic_Data',
			'jet-engine-query-builder' => '\Jet_Engine\Bricks_Views\Dynamic_Data',
		) );

		Dynamic_Data\Providers::register( $dynamic_data_providers );
	}

	public function component_path( $relative_path = '' ) {
		return jet_engine()->plugin_path( 'includes/components/bricks-views/' . $relative_path );
	}

	public function register_elements() {

		if ( ! class_exists( '\Jet_Engine\Bricks_Views\Elements\Base' ) ) {
			require $this->component_path( 'elements/base.php' );
		}
		
		require $this->component_path( 'helpers/options-converter.php' );
		require $this->component_path( 'helpers/controls-converter/base.php' );
		require $this->component_path( 'helpers/controls-converter/control-text.php' );
		require $this->component_path( 'helpers/controls-converter/control-select.php' );
		require $this->component_path( 'helpers/controls-converter/control-repeater.php' );
		require $this->component_path( 'helpers/controls-converter/control-checkbox.php' );
		require $this->component_path( 'helpers/controls-converter/control-default.php' );
		require $this->component_path( 'helpers/controls-converter/control-icon.php' );
		require $this->component_path( 'helpers/preview.php' );
		require $this->component_path( 'helpers/repeater.php' );
		require $this->component_path( 'helpers/controls-hook-bridge.php' );
		
		$element_files = array(
			$this->component_path( 'elements/listing-grid.php' ),
			$this->component_path( 'elements/dynamic-field.php' ),
			$this->component_path( 'elements/dynamic-image.php' ),
			$this->component_path( 'elements/dynamic-link.php' ),
			$this->component_path( 'elements/dynamic-terms.php' ),
		);

		foreach ( $element_files as $file ) {
			\Bricks\Elements::register_element( $file );
		}

		do_action( 'jet-engine/bricks-views/register-elements' );
	}

	/**
	 * Check if is Bricks editor render request
	 * 
	 * @return boolean [description]
	 */
	public function is_bricks_editor() {
		// is API request
		$bricks_request_str = 'wp-json/bricks/v1/render_element';
		$is_api = ( ! empty( $_SERVER['REQUEST_URI'] ) && false !== strpos( $_SERVER['REQUEST_URI'], $bricks_request_str ) );

		// is AJAX request
		$is_ajax = ( ! empty( $_REQUEST['action'] ) && 'bricks_render_element' === $_REQUEST['action'] );

		// Is editor iframe
		$is_editor = ( ! empty( $_REQUEST['bricks'] ) && 'run' === $_REQUEST['bricks'] );

		return $is_api || $is_ajax || $is_editor;
	}

	/**
	 * Retrieves request data for Bricks elements.
	 *
	 * This method handles different types of requests to fetch the necessary data
	 * for Bricks elements. It checks if the request is a REST call or an AJAX call
	 * specifically for rendering Bricks elements. If the data is found, it is
	 * returned as an associative array. Otherwise, the method returns false.
	 *
	 * @return array|false The request data as an associative array, or false if no data is found.
	 */
	public function get_request_data() {
		$data = false;

		if ( bricks_is_rest_call() ) {
			$data = file_get_contents( 'php://input' );
		} elseif ( wp_doing_ajax() && isset( $_REQUEST['action'] ) && 'bricks_render_element' === $_REQUEST['action'] ) {
			$data = $_REQUEST;
		}

		if ( ! $data ) {
			return false;
		}

		if ( is_string( $data ) ) {
			$data = json_decode( $data, true );
		}

		return $data;
	}

	public function is_bricks_listing( $listing_id ) {
		return jet_engine()->listings->data->get_listing_type( $listing_id ) === $this->listing->get_slug();
	}

	public function compat_tweaks() {
		// fix slider arrows bug for the listing grid
		add_filter( 'jet-engine/listing/grid/slider-options', function( $options ) {
			
			if ( ! empty( $_REQUEST['action'] ) && 'bricks_get_element_html' === $_REQUEST['action'] ) {
				$options['prevArrow'] = wp_slash( $options['prevArrow'] );
				$options['nextArrow'] = wp_slash( $options['nextArrow'] );
			}

			return $options;
		} );
	}

	public function add_arguments_to_gallery_slider( $args ) {
		array_push( $args['css_classes'], 'jet-engine-gallery-lightbox' );

		return $args;
	}

	public function add_arguments_to_gallery_grid( $args ) {
		array_push( $args['css_classes'], 'bricks-lightbox' );

		return $args;
	}

	public function add_attributes_to_gallery( $attr, $img_data ) {
		if ( in_array( 'is-lightbox', $attr['class'] ) ) {
			$key = array_search( 'is-lightbox', $attr['class'] );
			unset( $attr['class'][ $key ] );
		}

		$img_id   = $img_data['id'];

		$full_img_sizes  = self::get_full_img_sizes( $img_id );
		$full_img_width  = $full_img_sizes['width'];
		$full_img_height = $full_img_sizes['height'];

		$attr = array_merge( $attr,
			[
				'data-pswp-width'  => $full_img_width,
				'data-pswp-height' => $full_img_height,
			] );

		return $attr;
	}

	public static function get_full_img_sizes( $img_id = null ) {

		$result  = array();
		$img_src = wp_get_attachment_image_src( $img_id, 'full' );

		$result['width'] = $img_src[1];
		$result['height'] = $img_src[2];

		return $result;
	}

	// Adding repeater_field option to Dynamic Field sources
	// for use in Listing (source - custom_content_type_repeater)
	public function filter_controls( $controls ) {

		if ( array_key_exists( 'repeater_field', $controls['dynamic_field_source']['options'] ) ) {
			return $controls;
		}

		$post_id = isset( Database::$page_data['original_post_id'] ) ? Database::$page_data['original_post_id'] : Database::$page_data['preview_or_post_id'];

		if ( ! $post_id ) {
			return $controls;
		}

		$listing_data = get_post_meta( $post_id, '_listing_data', true );
		$allowed_sources = [ 'repeater', 'custom_content_type_repeater' ];

		if ( empty( $listing_data ) || ! in_array( $listing_data['source'], $allowed_sources )  ) {
			return $controls;
		}

		$controls['dynamic_field_source']['options']['repeater_field'] = esc_html__( 'Repeater Field', 'jet-engine' );

		return $controls;
	}

	public function has_bricks() {
		return ( defined( 'BRICKS_VERSION' ) && \Jet_Engine\Modules\Performance\Module::instance()->is_tweak_active( 'enable_bricks_views' ) );
	}
}
