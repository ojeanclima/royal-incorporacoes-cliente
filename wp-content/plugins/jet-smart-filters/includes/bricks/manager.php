<?php
/**
 * Bricks views manager
 */

namespace Jet_Smart_Filters\Bricks_Views;

define( 'BRICKS_QUERY_LOOP_PROVIDER_ID', 'bricks-query-loop' );
define( 'BRICKS_QUERY_LOOP_PROVIDER_NAME', 'Bricks query loop' );

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Manager class
 */
class Manager {

	/**
	 * Elementor Frontend instance
	 *
	 * @var null
	 */
	public $frontend = null;

	/**
	 * Dynamic tags component
	 */
	public $dynamic_tags;

	/**
	 * Constructor for the class
	 */
	function __construct() {
		if ( ! $this->has_bricks() ) {
			return;
		}

		add_action( 'init', [ $this, 'register_elements' ], 11 );
		add_action( 'init', [ $this, 'add_control_to_elements' ], 40 );
		add_action( 'init', [ $this, 'enqueue_styles_for_builder' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 9);
		add_action( 'jet-smart-filters/render/ajax/before', [ $this, 'register_bricks_dynamic_data_on_ajax' ] );
		add_action( 'init', array( $this, 'register_dynamic_tags' ), 90 );

		add_filter( 'bricks/builder/i18n', function ( $i18n ) {
			$i18n['jetsmartfilters'] = esc_html__( 'JetSmartFilters', 'jet-smart-filters' );

			return $i18n;
		} );

		add_action( 'jet-smart-filters/providers/register', [ $this, 'register_provider_for_filters' ] );
		add_filter( 'jet-engine/query-builder/filters/allowed-providers', [ $this, 'add_provider_to_query_builder' ] );
	}

	public function component_path( $relative_path = '' ) {
		return jet_smart_filters()->plugin_path( 'includes/bricks/' . $relative_path );
	}

	public function register_elements() {
		if ( ! class_exists( '\Jet_Engine\Bricks_Views\Elements\Base' ) ) {
			require $this->component_path( 'compatibility/elements/base.php' );
			require $this->component_path( 'compatibility/helpers/options-converter.php' );
			require $this->component_path( 'compatibility/helpers/controls-converter/base.php' );
			require $this->component_path( 'compatibility/helpers/controls-converter/control-text.php' );
			require $this->component_path( 'compatibility/helpers/controls-converter/control-select.php' );
			require $this->component_path( 'compatibility/helpers/controls-converter/control-repeater.php' );
			require $this->component_path( 'compatibility/helpers/controls-converter/control-checkbox.php' );
			require $this->component_path( 'compatibility/helpers/controls-converter/control-default.php' );
			require $this->component_path( 'compatibility/helpers/controls-converter/control-icon.php' );
			require $this->component_path( 'compatibility/helpers/preview.php' );
			require $this->component_path( 'compatibility/helpers/repeater.php' );
		}

		require $this->component_path( 'elements/base.php' );
		require $this->component_path( 'elements/base-checkbox.php' );

		$element_files = array(
			$this->component_path( 'elements/active-filters.php' ),
			$this->component_path( 'elements/active-tags.php' ),
			$this->component_path( 'elements/alphabet.php' ),
			$this->component_path( 'elements/apply-button.php' ),
			$this->component_path( 'elements/check-range.php' ),
			$this->component_path( 'elements/checkboxes.php' ),
			$this->component_path( 'elements/color-image.php' ),
			$this->component_path( 'elements/date-period.php' ),
			$this->component_path( 'elements/date-range.php' ),
			$this->component_path( 'elements/pagination.php' ),
			$this->component_path( 'elements/radio.php' ),
			$this->component_path( 'elements/range.php' ),
			$this->component_path( 'elements/rating.php' ),
			$this->component_path( 'elements/remove-filters.php' ),
			$this->component_path( 'elements/select.php' ),
			$this->component_path( 'elements/search.php' ),
			$this->component_path( 'elements/sorting.php' ),
			$this->component_path( 'elements/hidden.php' ),
		);

		foreach ( $element_files as $file ) {
			\Bricks\Elements::register_element( $file );
		}
	}

	public function enqueue_styles_for_builder() {
		if ( bricks_is_builder() ) {
			jet_smart_filters()->set_filters_used();

			// Add JetSmartFilters icons font
			wp_enqueue_style(
				'jet-smart-filters-icons-font',
				jet_smart_filters()->plugin_url( 'assets/css/lib/jet-smart-filters-icons/jet-smart-filters-icons.css' ),
				array(),
				jet_smart_filters()->get_version()
			);

			jet_smart_filters()->filter_types->filter_styles();
		}
	}

	public function enqueue_scripts() {
		wp_enqueue_script(
			'jet-smart-filters-bricks',
			jet_smart_filters()->plugin_url( 'assets/js/bricks.js' ),
			['jquery'],
			jet_smart_filters()->get_version(),
			true
		);
	}

	public function register_bricks_dynamic_data_on_ajax() {
		if ( ! function_exists( 'jet_engine' ) ) {
			// Backup if JetEngine is not installed
			global $wp_filter;
			if ( isset( $wp_filter['wp'][8] ) ) {
				foreach ( $wp_filter['wp'][8] as $callback ) {
					if ( is_array( $callback['function'] ) && is_object( $callback['function'][0] ) ) {
						if ( 'Bricks\Integrations\Dynamic_Data\Providers' === get_class( $callback['function'][0] ) ) {
							call_user_func( $callback['function'] );
							break;
						}
					}
				}
			}
		}
	}

	public function register_dynamic_tags() {
		require_once $this->component_path( 'dynamic-tags/manager.php' );

		$this->dynamic_tags = new Dynamic_Data\Manager();
	}

	public function has_bricks() {
		return defined( 'BRICKS_VERSION' );
	}

	public static function get_allowed_providers() {
		$provider_allowed = [
			'bricks-query-loop' => true,
		];

		if ( function_exists( 'jet_engine' ) ) {
			$provider_allowed = array_merge(
				$provider_allowed,
				[
					'jet-engine'          => true,
					'jet-engine-maps'     => jet_engine()->modules->is_module_active( 'maps-listings' ),
					'jet-engine-calendar' => jet_engine()->modules->is_module_active( 'calendar' ),
				]
			);
		}

		return apply_filters( 'jet-smart-filters/bricks/allowed-providers', $provider_allowed );
	}

	public function register_provider_for_filters( $providers_manager ) {
		$providers_manager->register_provider(
			'\Jet_Smart_Filters\Bricks_Views\Provider', // Custom provider class name
			jet_smart_filters()->plugin_path( 'includes/bricks/provider.php' ) // Path to file where this class defined
		);
	}

	public function add_control_to_elements() {
		// Only container, block and div element have query controls
		$elements = [ 'container', 'block', 'div' ];

		foreach ( $elements as $name ) {
			add_filter( "bricks/elements/{$name}/controls", [ $this, 'add_jet_smart_filters_controls' ], 40 );
		}
	}

	public function add_jet_smart_filters_controls( $controls ) {
		$jet_smart_filters_control['jsfb_is_filterable'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Is filterable', 'jet-smart-filters' ),
			'type'        => 'checkbox',
			'required'    => [
				[ 'hasLoop', '=', true ],
			],
			'rerender'    => true,
			'description' => esc_html__( 'Please check this option if you will use with JetSmartFilters.', 'jet-smart-filters' ),
		];

		$jet_smart_filters_control['jsfb_query_id'] = [
			'tab'            => 'content',
			'label'          => esc_html__( 'Query ID for filters', 'jet-smart-filters' ),
			'type'           => 'text',
			'placeholder'    => esc_html__( 'Please enter query id.', 'jet-smart-filters' ),
			'hasDynamicData' => false,
			'required'       => [
				[ 'hasLoop', '=', true ],
				[ 'jsfb_is_filterable', '=', true ]
			],
			'rerender'       => true,
		];

		// Below 2 lines is just some php array functions to force my new control located after the query control
		$query_key_index = absint( array_search( 'query', array_keys( $controls ) ) );
		$new_controls    = array_slice( $controls, 0, $query_key_index + 1, true ) + $jet_smart_filters_control + array_slice( $controls, $query_key_index + 1, null, true );

		return $new_controls;
	}

	public function add_provider_to_query_builder( $providers ) {
		$providers[] = BRICKS_QUERY_LOOP_PROVIDER_ID;

		return $providers;
	}
}