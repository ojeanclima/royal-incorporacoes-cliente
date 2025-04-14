<?php
namespace Jet_Engine\Listings\Components;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define component instance class
 */
class Component {

	protected $id;
	protected $name;
	protected $props;
	protected $styles;
	protected $views;
	protected $render_view;
	protected $category;
	protected $status;
	
	protected $raw_meta   = null;
	protected $tmp_object = null;

	public function __construct( $post ) {
		
		$this->id          = $post->ID;
		$this->name        = $post->post_title;
		$this->status      = $post->post_status;
		$this->props       = $this->get_meta( '_component_props', [] );
		$this->styles      = $this->get_meta( '_component_styles', [] );
		$this->views       = $this->get_meta( '_component_views', [ 'elementor', 'bricks', 'blocks' ] );
		$this->render_view = $this->get_meta( '_listing_type', 'blocks' );
		$this->category    = $this->get_meta( '_component_category', jet_engine()->listings->components->components_category( 'slug' ) );

		do_action( 'jet-engine/component/init', $this );
	}

	/**
	 * Allow to export component as array
	 * 
	 * @return [type] [description]
	 */
	public function to_array() {
		return [
			'id'          => $this->get_id(),
			'name'        => $this->get_display_name(),
			'status'      => $this->get_status(),
			'props'       => $this->get_props(),
			'styles'      => $this->get_styles(),
			'views'       => $this->get_views(),
			'render_view' => $this->get_render_view(),
			'category'    => $this->get_category(),
		];
	}

	/**
	 * Returns ID
	 * 
	 * @return [type] [description]
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Returns component status
	 * 
	 * @return [type] [description]
	 */
	public function get_status() {
		return $this->status;
	}

	/**
	 * Returns unique element name for current component
	 * 
	 * @return [type] [description]
	 */
	public function get_element_name() {
		return jet_engine()->listings->components->get_component_base_name() . '-' . $this->get_id();
	}

	/**
	 * Returns name
	 * 
	 * @return [type] [description]
	 */
	public function get_display_name() {
		return $this->name;
	}

	/**
	 * Returns props
	 *
	 * Single prop format:
	 * [
	 * 	'control_label'         => 'Text Control',
	 * 	'control_name'          => 'text_control',
	 * 	'control_type'          => 'text',
	 * 	'control_options'       => '',
	 * 	'control_default'       => 'Default Value',
	 * 	'control_default_image' => false,
	 * ]
	 * 
	 * @return [type] [description]
	 */
	public function get_props() {
		return $this->sanitize_props( $this->props );
	}

	/**
	 * Returns styling props
	 *
	 * Single prop format:
	 * [
	 * 	'control_label'         => 'Text Control',
	 * 	'control_name'          => 'text_control',
	 * 	'control_default'       => 'Default Value',
	 * ]
	 * 
	 * @return [type] [description]
	 */
	public function get_styles() {
		return $this->sanitize_props( $this->styles, 'color' );
	}

	/**
	 * Sanitize props
	 * 
	 * @param  array  $props [description]
	 * @return [type]        [description]
	 */
	public function sanitize_props( $props = [], $default_type = 'text' ) {
		
		if ( ! empty( $props ) ) {
			foreach ( $props as $i => $prop ) {
				$prop['control_type'] = ! empty( $prop['control_type'] ) ? $prop['control_type'] : $default_type;
				$props[ $i ] = $prop;
			}
		}

		return $props;
	}

	/**
	 * Returns views
	 * 
	 * @return [type] [description]
	 */
	public function get_views() {
		return apply_filters( 'jet-engine/listings/components/component/views', $this->views, $this );
	}

	/**
	 * Returns render view
	 * 
	 * @return [type] [description]
	 */
	public function get_render_view() {
		return $this->render_view;
	}

	/**
	 * Returns category of the component
	 * 
	 * @return [type] [description]
	 */
	public function get_category() {
		return $this->category;
	}

	/**
	 * Get component meta
	 * 
	 * @param  [type] $key     [description]
	 * @param  [type] $default [description]
	 * @return [type]          [description]
	 */
	public function get_meta( $key = null, $default = false ) {
		
		if ( null === $this->raw_meta ) {
			
			$this->raw_meta = get_post_meta( $this->get_id() );
			
			// unset meta to not stroe big amount of not needed information
			$unset_meta = [ '_elementor_data', '_elementor_controls_usage' ];

			foreach ( $unset_meta as $unset_key ) {
				if ( isset( $this->raw_meta[ $unset_key ] ) ) {
					unset( $this->raw_meta[ $unset_key ] );
				}
			}

		}

		if ( isset( $this->raw_meta[ $key ] ) ) {
			return maybe_unserialize( $this->raw_meta[ $key ][0] );
		} else {
			return $default;
		}

	}

	/**
	 * Returns prop data formatted to use as appropriate control definition
	 * 
	 * @param  array $prop      Property data
	 * @param  array $types_map Optional - map of control type names for appropriate builder
	 * @return array 
	 */
	public function get_prop_for_control( $prop = [], $types_map = [] ) {

		$default = $this->get_prop_default( $prop );
		$type    = isset( $prop['control_type'] ) ? $prop['control_type'] : 'text';

		$control_data = [
			'label' => $prop['control_label'],
			'type'  => isset( $types_map[ $type ] ) ? $types_map[ $type ] : $type,
		];

		if ( ! empty( $default ) ) {
			$control_data['default'] = $default;
		}

		if ( 'select' === $type ) {
			$control_data['options'] = $this->get_prop_options( $prop['control_options'] );
		}

		return $control_data;

	}

	/**
	 * Parse options string into PHP array
	 * 
	 * @param  string $options_string 
	 * @return [type]                 [description]
	 */
	public function get_prop_options( $options_string = '' ) {

		$raw    = preg_split( '/\r\n|\r|\n/', $options_string );
		$result = [];

		if ( empty( $raw ) ) {
			return $result;
		}

		foreach ( $raw as $raw_value ) {
			$parsed_value = explode( '::', trim( $raw_value ) );
			$result[ $parsed_value[0] ] = isset( $parsed_value[1] ) ? $parsed_value[1] : $parsed_value[0];
		}

		return $result;

	}

	/**
	 * Get default value of the prop depending on prop type
	 * 
	 * @param  array  $prop [description]
	 * @return [type]       [description]
	 */
	public function get_prop_default( $prop = [], $args = [] ) {

		$type    = isset( $prop['control_type'] ) ? $prop['control_type'] : 'text';
		$default = '';
		$args    = array_merge( [
			'media_format' => 'all'
		], $args );

		switch ( $type ) {
			case 'media':
				
				$image = isset( $prop['control_default_image'] ) ? $prop['control_default_image'] : false;

				if ( $image && ! is_array( $image ) ) {
					$image = false;
				}

				if ( ! $image ) {
					$default = false;
				} else {
					$default = ( 'all' === $args['media_format'] ) ? $image : $image[ $args['media_format'] ];
				}

				$default = ( $default && is_array( $default ) ) ? $default : false;

				break;
			
			default:
				$default = isset( $prop['control_default'] ) ? $prop['control_default'] : '';
				break;
		}

		return $default;

	}

	/**
	 * Set component props
	 *
	 * Single prop format:
	 * [
	 * 	'control_label'         => 'Text Control',
	 * 	'control_name'          => 'text_control',
	 * 	'control_type'          => 'text',
	 * 	'control_options'       => '',
	 * 	'control_default'       => 'Default Value',
	 * 	'control_default_image' => false,
	 * ]
	 * 
	 * @param array $props [description]
	 */
	public function set_props( $props = [] ) {
		$this->props = $props;
		update_post_meta( $this->get_id(), '_component_props', $props );
	}

	/**
	 * Set component props
	 *
	 * Single prop format:
	 * [
	 * 	'control_label'         => 'Text Control',
	 * 	'control_name'          => 'text_control',
	 * 	'control_default'       => 'Default Value',
	 * ]
	 * 
	 * @param array $props [description]
	 */
	public function set_styles( $props = [] ) {

		foreach ( $props as $key => $value ) {
			// at the moment only color allowed for style controls, so we're forcing to set control_type as color
			$value['control_type'] = 'color';
			$props[ $key ] = $value;
		}

		$this->styles = $props;

		update_post_meta( $this->get_id(), '_component_styles', $props );
	}

	/**
	 * Returns default state of the component
	 * 
	 * @param  boolean $props [description]
	 * @return [type]         [description]
	 */
	public function get_default_state( $props = false, $args = [] ) {
		
		if ( false === $props ) {
			$props = array_merge( $this->get_props(), $this->get_styles() );
		}

		$state = [];

		foreach ( $props as $prop ) {
			if ( ! empty( $prop['control_name'] ) ) {
				$default_value = $this->get_prop_default( $prop, $args );
				$state[ $prop['control_name'] ] = $default_value;
			}
		}

		return $state;

	}

	/**
	 * Check if given view is allowed by component
	 * 
	 * @return boolean [description]
	 */
	public function is_view_supported( $view ) {
		return ( $view && in_array( $view, $this->get_views() ) ) ? true : false;
	}

	/**
	 * Set compoonent object by given context
	 * 
	 * @param string $context [description]
	 */
	public function set_component_context( $context = '' ) {

		if ( ! $context ) {
			$context = jet_engine()->listings->components->state->get( 'component_context' );
		}
		
		$this->tmp_object = null;

		if ( $context && 'default_object' !== $context ) {

			$new_object = jet_engine()->listings->data->get_object_by_context( $context );

			if ( $new_object ) {

				$this->tmp_object = jet_engine()->listings->data->get_current_object();
				jet_engine()->listings->data->set_current_object( $new_object );

				/**
				 * Fires after component sets a custom context
				 */
				do_action( 'jet-engine/component/set-object', $new_object, $this->tmp_object );
			}

		}
	}

	/**
	 * Render component content
	 * 
	 * @return [type] [description]
	 */
	public function get_content( $settings = [], $with_context = false ) {

		ob_start();

		jet_engine()->listings->components->state->set( $settings );

		if ( $with_context ) {
			$this->set_component_context();
		}

		do_action( 'jet-engine/component/before-content', $this );

		/**
		 * @see https://github.com/Crocoblock/issues-tracker/issues/13186
		 */
		$component_id = apply_filters( 'jet-engine/component/render-id', $this->get_id(), $this );

		echo jet_engine()->frontend->get_listing_item_content( $component_id );

		// Reset current object to initial if component context was used
		if ( $this->tmp_object ) {

			/**
			 * Fires after component resesets a custom context
			 */
			do_action(
				'jet-engine/component/reset-object',
				jet_engine()->listings->data->get_current_object(),
				$this->tmp_object
			);

			jet_engine()->listings->data->set_current_object( $this->tmp_object );
		}

		do_action( 'jet-engine/component/after-content', $this );

		jet_engine()->listings->components->state->reset();

		jet_engine()->admin_bar->register_item( 'edit_post_' . $this->get_id(), array(
			'title'     => get_the_title( $this->get_id() ),
			'sub_title' => esc_html__( 'Component', 'jet-engine' ),
			'href'      => jet_engine()->post_type->admin_screen->get_edit_url( 
				$this->get_render_view(), $this->get_id() 
			),
		) );

		$content = ob_get_clean();

		return $content;
	}

	/**
	 * Returns style tag with allowed CSS variables
	 * 
	 * @param  [type] $settings     [description]
	 * @param  [type] $css_selector [description]
	 * @return [type]               [description]
	 */
	public function css_variables_tag( $settings = [], $css_selector = 'body' ) {

		$css_string = $this->css_variables_string( $settings );

		if ( ! empty( $css_string ) ) {
			return sprintf( '<style type="text/css">%1$s {%2$s}</style>', $css_selector, $css_string );
		} else {
			return '';
		}
		
	}

	/**
	 * Get prefix of CSS variable
	 * 
	 * @return [type] [description]
	 */
	public static function css_var_prefix() {
		return '--jet-component-';
	}

	/**
	 * Get component CSS varaiabled string based on settings to use as inline attribue or in style tag
	 * 
	 * @param  array  $settings [description]
	 * @return [type]           [description]
	 */
	public function css_variables_string( $settings = [] ) {

		$styles   = $this->get_styles();
		$css_vars = [];
		$default_state = $this->get_default_state();

		foreach ( $styles as $style ) {

			if ( empty( $style['control_name'] ) ) {
				continue;
			}

			$control_name = $style['control_name'];
			$value = ! empty( $settings[ $control_name ] ) ? $settings[ $control_name ] : false;

			if ( ! $value ) {
				$value = ! empty( $default_state[ $control_name ] ) ? $default_state[ $control_name ] : false;
			}

			if ( $value ) {

				if ( is_array( $value ) && isset( $value['hex'] ) ) {
					$value = $value['hex'];
				} elseif ( is_array( $value ) && isset( $value['raw'] ) ) {
					$value = $value['raw'];
				}

				$css_vars[] = sprintf(  '%3$s%1$s:%2$s;', $control_name, $value, self::css_var_prefix() );
			}
		}

		if ( ! empty( $css_vars ) ) {
			return implode( '', $css_vars );
		} else {
			return '';
		}
	}

}
