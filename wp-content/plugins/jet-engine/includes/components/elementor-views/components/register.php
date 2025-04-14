<?php
namespace Jet_Engine\Elementor_Views\Components;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define components registry class
 */
class Register {

	protected $category_registered = false;

	public function __construct() {

		add_filter(
			'jet-engine/twig-views/editor/css-variables',
			[ $this, 'register_css_vars_for_component_controls' ]
		);

		add_filter( 'jet-engine/listings/document-id', [ $this, 'set_components_document_id' ] );

		add_action(
			'jet-engine/listings/components/register-component-elements',
			[ $this, 'add_widget_for_component' ]
		);

		add_action( 'jet-engine/listings/components/update-settings', [ $this, 'on_settings_update' ] );
		add_action( 'jet-engine/elementor-views/documents-registered', [ $this, 'register_document' ] );
		add_action( 'elementor/document/after_save', [ $this, 'save_component_meta' ], 10, 2 );
		add_action( 'jet-engine/elementor-views/dynamic-tags/register', [ $this, 'register_tags' ] );
		add_action( 'jet-engine/component/before-content', [ $this, 'print_block_preview_css' ] );

		if ( ! $this->category_registered ) {
			add_action(
				'elementor/elements/categories_registered',
				[ $this, 'register_components_category' ]
			);
		}

	}

	/**
	 * Print Elementor CSS for Elementor-created component in block editor
	 *
	 * @return [type] [description]
	 */
	public function print_block_preview_css( $component ) {

		if ( 'elementor' !== $component->get_render_view() ) {
			return;
		}

		if ( empty( $_REQUEST['is_component_preview'] ) ) {
			return;
		}

		\Elementor\Plugin::instance()->frontend->register_styles();
		\Elementor\Plugin::instance()->frontend->enqueue_styles();

		wp_print_styles();
	}

	/**
	 * Synch elementor controls with component settings
	 *
	 * @param  [type] $component [description]
	 * @return [type]            [description]
	 */
	public function on_settings_update( $component ) {

		if ( 'elementor' !== $component->get_render_view() ) {
			return;
		}

		$props = $component->get_props();
		$styles = $component->get_styles();

		if ( ! empty( $props ) ) {
			foreach ( $props as $i => $prop ) {
				if ( empty( $prop['_id'] ) && ! empty( $prop['id'] ) ) {
					$props[ $i ]['_id'] = $prop['id'];
				}

			}
		}

		if ( ! empty( $styles ) ) {
			foreach ( $styles as $i => $prop ) {
				if ( empty( $prop['_id'] ) && ! empty( $prop['id'] ) ) {
					$styles[ $i ]['_id'] = $prop['id'];
				}

			}
		}

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
	 * Regsiter CSS variables for component controls
	 * @return [type] [description]
	 */
	public function register_css_vars_for_component_controls( $css_vars = [] ) {

		$kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit();

		if ( ! $kit ) {
			return $css_vars;
		}

		$system_colors = $kit->get_settings( 'system_colors' );
		$custom_colors = $kit->get_settings( 'custom_colors' );

		if ( ! empty( $system_colors ) ) {
			foreach ( $system_colors as $color ) {
				$css_vars[] = [
					'var'   => sprintf( 'var( --e-global-color-%1$s )', $color['_id'] ),
					'value' => $color['color'],
				];
			}
		}

		if ( ! empty( $custom_colors ) ) {
			foreach ( $custom_colors as $color ) {
				$css_vars[] = [
					'var'   => sprintf( 'var( --e-global-color-%1$s )', $color['_id'] ),
					'value' => $color['color'],
				];
			}
		}

		return $css_vars;
	}

	/**
	 * Register dynamic tags to get component prop
	 * 
	 * @param  [type] $tags_module [description]
	 * @return [type]              [description]
	 */
	public function register_tags( $tags_module ) {

		require jet_engine()->plugin_path( 'includes/components/elementor-views/components/dynamic-tags/text-tag.php' );
		require jet_engine()->plugin_path( 'includes/components/elementor-views/components/dynamic-tags/image-tag.php' );
		require jet_engine()->plugin_path( 'includes/components/elementor-views/components/dynamic-tags/color-tag.php' );

		$tags_module->register( new Dynamic_Tags\Text_Tag() );
		$tags_module->register( new Dynamic_Tags\Image_Tag() );
		$tags_module->register( new Dynamic_Tags\Color_Tag() );

	}


	/**
	 * Save component meta into a separate meta field on elementor component document save
	 * 
	 * @param  [type] $document [description]
	 * @param  [type] $data     [description]
	 * @return [type]           [description]
	 */
	public function save_component_meta( $document, $data ) {
		
		if ( $document->get_name() !== jet_engine()->listings->components->get_component_base_name() ) {
			return;
		}

		$component = jet_engine()->listings->components->get(
			jet_engine()->listings->components->get_component_base_name() . '-' . $document->get_main_id()
		);

		if ( $component && ! empty( $data['settings']['component_controls_list'] ) ) {
			$component->set_props( $data['settings']['component_controls_list'] );
		}

		if ( $component && ! empty( $data['settings']['component_style_controls_list'] ) ) {
			$component->set_styles( $data['settings']['component_style_controls_list'] );
		}

	}

	/**
	 * Regster component document
	 * 
	 * @return [type] [description]
	 */
	public function register_document( $documents_manager ) {

		require jet_engine()->plugin_path( 'includes/components/elementor-views/components/component-document.php' );

		$documents_manager->register_document_type(
			jet_engine()->listings->components->get_component_base_name(),
			'\Jet_Engine\Elementor_Views\Components\Document'
		);

	}

	/**
	 * Set document ID for component
	 * 
	 * @param [type] $id [description]
	 */
	public function set_components_document_id( $id ) {

		if ( ! empty( $_REQUEST['listing_view_type'] )
			&& 'elementor' === $_REQUEST['listing_view_type']
			&& ! empty( $_REQUEST['template_entry_type'] )
			&& 'component' === $_REQUEST['template_entry_type']
		) {
			return jet_engine()->listings->components->get_component_base_name();
		}

		return $id;
	}

	/**
	 * Register separate widget for each component
	 * @param [type] $component [description]
	 */
	public function add_widget_for_component( $component ) {

		if ( ! $component->is_view_supported( 'elementor' ) ) {
			return;
		}

		add_action( 'elementor/widgets/register', function( $widgets_manager ) use ( $component ) {

			if ( ! class_exists( '\Jet_Engine\Elementor_Views\Components\Base_Widget' ) ) {
				require jet_engine()->plugin_path( 'includes/components/elementor-views/components/base-widget.php' );
			}

			$component_widget = new Base_Widget( [], null, $component );

			$widgets_manager->register( $component_widget );

		} );

	}

	/**
	 * Register components category 
	 * 
	 * @param  [type] $elements_manager [description]
	 * @return [type]                   [description]
	 */
	public function register_components_category( $elements_manager ) {

		$elements_manager->add_category(
			jet_engine()->listings->components->components_category( 'slug' ),
			[
				'title' => jet_engine()->listings->components->components_category( 'name' ),
				'icon' => 'fa fa-plug',
			]
		);

		$this->category_registered = true;

	}

}
