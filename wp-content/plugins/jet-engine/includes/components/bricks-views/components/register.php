<?php
namespace Jet_Engine\Bricks_Views\Components;

use \Jet_Engine\Bricks_Views\Listing\Assets as Listing_Assets;

class Register {

	private $_component_elements = [];
	private $payload = null;
	private $block_editor_assets_enqueued = false;

	public function __construct() {

		add_filter( 'jet-engine/bricks-views/dynamic_data/register_providers', [ $this, 'add_dynamic_data_provider' ] );
		add_filter( 'jet-engine/listings/dynamic-image/image-data', [ $this, 'adjust_dynamic_image_data' ], 1, 2 );
		
		add_action( 'jet-engine/bricks-views/setup-preview', [ $this, 'setup_preview_state' ] );
		add_action( 'jet-engine/listings/components/register-component-elements', [ $this, 'register_component_el' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'print_preview_vars' ], 10 );
		add_action( 'wp', [ $this, 'load_component_el' ], 11 );
		add_action( 'rest_api_init', [ $this, 'rest_load_component_el' ] );

		add_filter( 'bricks/dynamic_data/format_value', [ $this, 'process_dynamic_tag' ], 10, 3 );

		/**
		 * Fix Ajax popups rendering, when popup trggered from component
		 * https://github.com/Crocoblock/issues-tracker/issues/11661 (1)
		 */
		add_filter( 'bricks/frontend/render_data', [ $this, 'fix_ajax_popups_render' ], 0, 3 );
		add_action( 'jet-engine/component/after-content', [ $this, 'fix_ajax_popup_css' ] );

	}

	/**
	 * Fix CSS generation on Bricks AJAX popup loading
	 * https://github.com/Crocoblock/issues-tracker/issues/11661 (1)
	 */
	public function fix_ajax_popup_css() {

		if ( ! \Bricks\Api::is_current_endpoint( 'load_popup_content' ) ) {
			return;
		}

		if ( isset( Listing_Assets::$unique_inline_css )
			&& ! empty( Listing_Assets::$unique_inline_css )
		) {
			printf(
				'<style type="text/css">%s</style>',
				str_replace( '.brx-popup', '', implode( '', Listing_Assets::$unique_inline_css ) )
			);
		}
	}


	/**
	 * Fix Ajax popups rendering, when popup trggered from component
	 * https://github.com/Crocoblock/issues-tracker/issues/11661 (1)
	 */
	public function fix_ajax_popups_render( $content, $post, $area ) {

		if ( ! \Bricks\Api::is_current_endpoint( 'load_popup_content' ) ) {
			return $content;
		}

		$payload = $this->get_payload();

		if ( ! $payload ) {
			return $content;
		}

		$payload = json_decode( $payload, true );

		if ( ! $payload || ! isset( $payload['popupLoopId'] ) ) {
			return $content;
		}

		$loop_id = $payload['popupLoopId'];

		if ( isset( \Bricks\Popups::$ajax_popup_contents[ $loop_id ] ) ) {

			$new_loop_id = explode( ':', $loop_id );
			$new_loop_id[1] = 0;
			$new_loop_id = implode( ':', $new_loop_id );

			if ( ! isset( \Bricks\Popups::$ajax_popup_contents[ $new_loop_id ] ) ) {
				\Bricks\Popups::$ajax_popup_contents[ $new_loop_id ] = \Bricks\Popups::$ajax_popup_contents[ $loop_id ];
			}

		}

		return $content;

	}

	/**
	 * Print variables for component preview
	 * 
	 * @return [type] [description]
	 */
	public function print_preview_vars() {
		
		if ( ! bricks_is_builder_iframe() ) {
			return;
		}

		$post_id   = get_the_ID();

		if ( ! $post_id ) {
			return;
		}

		$component = jet_engine()->listings->components->get( $post_id, 'id' );

		if ( ! $component ) {
			return;
		}

		echo $component->css_variables_tag();

	}

	/**
	 * Adjust dynamic image data returned by Bricks dynamic tokens
	 * 
	 * @return [type] [description]
	 */
	public function adjust_dynamic_image_data( $result, $settings ) {
		if ( is_array( $result ) && ! empty( $result['useDynamicData'] ) ) {
			$result = [
				'image_tag' => $result['useDynamicData'],
			];
		}

		return $result;
	}

	/**
	 * Get payload of current request
	 * 
	 * @return [type] [description]
	 */
	public function get_payload() {
		
		if ( null === $this->payload ) {
			$this->payload = file_get_contents( 'php://input' );
		}

		return $this->payload;
	}

	/**
	 * Check if component is rendered by Bricks and supports blocks view 
	 * and block editor assets not enqueued yet - we need to enqueue bricks assets inside block editor
	 * to make sure component preview will be rendered correctly
	 */
	public function maybe_enqueue_block_editor_assets( $component ) {

		if ( 
			! $component->is_view_supported( 'blocks' )
			|| 'bricks' !== $component->get_render_view()
			|| $this->block_editor_assets_enqueued
		) {
			return;
		}

		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_assets' ] );
		$this->block_editor_assets_enqueued = true;

	}

	/**
	 * Enqueue bricks assets for blocks editor to make sure component preview will be rendered correctly
	 * @return [type] [description]
	 */
	public function enqueue_block_editor_assets() {
		if ( defined( 'BRICKS_URL_ASSETS' ) && defined( 'BRICKS_PATH_ASSETS' ) ) {
			wp_enqueue_style( 'bricks-frontend', BRICKS_URL_ASSETS . 'css/frontend-light.min.css', [], filemtime( BRICKS_PATH_ASSETS . 'css/frontend-light.min.css' ) );
		}
	}

	/**
	 * Register component elements
	 * 
	 * @return [type] [description]
	 */
	public function register_component_el( $component ) {

		if ( ! $component->is_view_supported( 'bricks' ) ) {
			return;
		}

		$this->maybe_enqueue_block_editor_assets( $component );

		if ( ! class_exists( '\Jet_Engine\Bricks_Views\Elements\Base' ) ) {
			require jet_engine()->bricks_views->component_path( 'elements/base.php' );
		}

		if ( ! class_exists( '\Jet_Engine\Bricks_Views\Components\Base_Element' ) ) {
			require jet_engine()->bricks_views->component_path( 'components/base-element.php' );
		}

		$element_instance = new Base_Element( null, $component );

		$this->_component_elements[] = $element_instance;

		// Regsiter early for the correct component rendering on editor AJAX calls
		$payload = $this->get_payload();

		if ( $payload && ( 
			str_contains( $payload, 'bricks_render_data' ) 
			|| ( str_contains( $payload, 'action=' ) 
				&& ( str_contains( $payload, 'bricks_' ) || str_contains( $payload, 'jet_' ) )
		) ) ) {

			add_action( 'init', function() use ( $element_instance ) {
				// Set controls
				$element_instance->load();

				\Bricks\Elements::$elements[ $element_instance->name ] = [
					'class'         => '\Jet_Engine\Bricks_Views\Components\Base_Element',
					'name'          => $element_instance->name,
					'label'         => $element_instance->label,
					'controls'      => $element_instance->controls,
					'controlGroups' => $element_instance->control_groups,
					'scripts'       => $element_instance->scripts,
				];
			}, 100 );

		}

	}

	/**
	 * Load elements on rest API request
	 * 
	 * @return [type] [description]
	 */
	public function rest_load_component_el() {
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			$this->load_component_el();
		}
	}

	/**
	 * Load component elements
	 * 
	 * @return [type] [description]
	 */
	public function load_component_el() {

		if ( ! empty( $this->_component_elements ) ) {
			
			foreach ( $this->_component_elements as $element_instance ) {

				// Set controls
				$element_instance->load();

				\Bricks\Elements::$elements[ $element_instance->name ] = [
					'class'            => '\Jet_Engine\Bricks_Views\Components\Base_Element',
					'name'             => $element_instance->name,
					'icon'             => $element_instance->icon,
					'category'         => $element_instance->category,
					'label'            => $element_instance->label,
					'keywords'         => $element_instance->keywords,
					'tag'              => $element_instance->tag,
					'controls'         => $element_instance->controls,
					'controlGroups'    => $element_instance->control_groups,
					'scripts'          => $element_instance->scripts,
					'block'            => $element_instance->block ? $element_instance->block : null,
					'draggable'        => $element_instance->draggable,
					'deprecated'       => $element_instance->deprecated,
					'panelCondition'   => $element_instance->panel_condition,

					// @since 1.5 (= Nestable element)
					'nestable'         => $element_instance->nestable,
					'nestableItem'     => $element_instance->nestable_item,
					'nestableChildren' => $element_instance->nestable_children,
				];

				/**
				 * Rendered HTML output for nestable non-layout elements (slider, accordion, tabs, etc.)
				 *
				 * To use inside BricksNestable.vue on mount()
				 *
				 * @since 1.5
				 */

				// Use specific Vue component to render element on canvas (@since 1.5)
				if ( $element_instance->vue_component ) {
					\Bricks\Elements::$elements[ $element_instance->name ]['component'] = $element_instance->vue_component;
				}

				// To distinguish non-layout nestables (slider-nested, etc.) in Vue render (@since 1.5)
				if ( ! $element_instance->is_layout_element() ) {
					\Bricks\Elements::$elements[ $element_instance->name ]['nestableHtml'] = $element_instance->nestable_html;
				}

				// Nestable element (@since 1.5)
				if ( $element_instance->nestable ) {
					// Always run certain scripts
					\Bricks\Elements::$elements[ $element_instance->name ]['scripts'][] = 'bricksBackgroundVideoInit';
				}

				// Provide 'attributes' data in builder
				if ( count( $element_instance->attributes ) ) {
					\Bricks\Elements::$elements[ $element_instance->name ]['attributes'] = $element_instance->attributes;
				}

				// Enqueue elements scripts in the builder iframe
				if ( bricks_is_builder_iframe() ) {
					$element_instance->enqueue_scripts();
				}
			}
		}
	}

	/**
	 * Register Dynamic Data providers for Component Values
	 *
	 * @param array $providers List of registered providers
	 */
	public function add_dynamic_data_provider( $providers = [] ) {

		require jet_engine()->bricks_views->component_path( 'components/provider-component-prop.php' );
		$providers['component-prop'] = '\Jet_Engine\Bricks_Views\Components';

		return $providers;
	}

	/**
	 * Set preview state for components
	 * 
	 * @param int $post_id Rendered listing/component ID
	 */
	public function setup_preview_state( $post_id ) {

		if ( ! $post_id || ! jet_engine()->listings->components->is_component( $post_id ) ) {
			return;
		}

		$component = jet_engine()->listings->components->get( $post_id, 'id' );

		jet_engine()->listings->components->state->set( 
			$component->get_default_state( false, [ 'media_format' => 'id' ] ) 
		);

	}

	/**
	 * Processes the given value based on the provided tag, post ID, filters, and context.
	 *
	 * @param mixed  $value     The initial value to be processed.
	 * @param string $tag       The tag associated with the value.
	 * @param int    $post_id   The ID of the post used for rendering.
	 *
	 * @return mixed The processed value, which may be modified based on the dynamic tag.
	 */
	public function process_dynamic_tag( $value, $tag, $post_id ) {
		if ( ! str_contains( $tag, 'jet_component__' ) ) {
			return $value;
		}

		if ( ! $this->contains_dynamic_tag( $value ) ) {
			return $value;
		}

		$tag = str_replace( [ '{', '}' ], '', $value[0] );

		return apply_filters(
			'bricks/dynamic_data/render_tag',
			$tag,
			get_post( $post_id ),
			'image'
		);
	}

	/**
	 * Checks if an array contains any dynamic tags.
	 *
	 * @param array $array The array to check for dynamic tags.
	 *
	 * @return bool Returns true if at least one element contains a dynamic tag, otherwise, returns false.
	 */
	public function contains_dynamic_tag( $array ) {
		if ( ! is_array( $array ) ) {
			return false; // Якщо не масив, просто повертаємо false
		}

		foreach ( $array as $value ) {
			// Перевірка, чи містить елемент тег у фігурних дужках
			if ( preg_match( '/\{[^\}]+\}/', $value ) ) {
				return true;
			}
		}

		return false;
	}

}
