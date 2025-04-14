<?php
namespace Jet_Engine\Modules\Profile_Builder;

class Elementor_Integration extends Base_Integration {

	/**
	 * Constructor for the class
	 */
	public function __construct() {

		add_action( 'jet-engine/listings/dynamic-link/source-controls', [ $this, 'register_link_controls' ], 10 );
		add_action( 'jet-engine/listings/dynamic-image/link-source-controls', [ $this, 'register_img_link_controls' ], 10 );

		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
			return;
		}

		add_action( 'jet-engine/elementor-views/widgets/register', [ $this, 'register_widgets' ], 11, 2 );

		add_action( 'jet-engine/elementor-views/dynamic-tags/register', [ $this, 'register_dynamic_tags' ], 10, 2 );
		add_action( 'jet-engine/profile-builder/template/assets', [ $this, 'enqueue_template_styles' ] );

		add_filter( 'jet-engine/profile-builder/template/content', [ $this, 'render_template_content' ], 0, 4 );
		add_filter( 'jet-engine/profile-builder/settings/template-sources', [ $this, 'register_templates_source' ] );

		add_filter( 
			'jet-engine/profile-builder/create-template/elementor_library',
			[ $this, 'create_profile_template' ],
			10, 3
		);

	}

	public function create_profile_template( $result = [], $template_name = '', $template_view = '' ) {

		if ( ! $template_name ) {
			return $result;
		}

		$template_id = wp_insert_post( [
			'post_title' => $template_name,
			'post_type'   => 'elementor_library',
			'post_status' => 'publish',
		] );

		if ( ! $template_id ) {
			return $result;
		}

		update_post_meta(
			$template_id,
			'_elementor_source',
			'post'
		);

		update_post_meta(
			$template_id,
			'_elementor_edit_mode',
			'builder'
		);

		update_post_meta(
			$template_id,
			'_elementor_template_type',
			'container'
		);

		$document = \Elementor\Plugin::instance()->documents->get( $template_id );

		$template_url = ( $document ) ? $document->get_edit_url() : add_query_arg( [ 
			'post'   => $template_id,
			'action' => 'elementor' 
		], admin_url( 'post.php' ) );

		return [
			'template_url' => $template_url,
			'template_id'  => $template_id,
		];

	}

	/**
	 * Add elementor templates to allowed profile builder templates
	 * 
	 * @param  array $sources Initial sources list
	 * @return array
	 */
	public function register_templates_source( $sources ) {
		$sources['elementor_library'] = __( 'Elementor Template', 'jet-engine' );
		return $sources;
	}

	/**
	 * Check if profile template is Elementor template, render it with Elementor
	 *
	 * @param  string $content     Initial content
	 * @param  int    $template_id template ID to render
	 * @return string
	 */
	public function render_template_content( $content, $template_id, $frontend, $template ) {

		if ( 'elementor_library' !== $template->post_type ) {
			return $content;
		}
		
		$elementor_content = \Elementor\Plugin::instance()->frontend->get_builder_content( $template_id );

		if ( $elementor_content ) {
			remove_all_filters( 'jet-engine/profile-builder/template/content' );
			return $elementor_content;
		}

		return $content;
	}

	/**
	 * Enqueue profile template assets
	 *
	 * @param  [type] $template_id [description]
	 * @return [type]              [description]
	 */
	public function enqueue_template_styles( $template_id ) {

		\Elementor\Plugin::instance()->frontend->enqueue_styles();

		$css_file = new \Elementor\Core\Files\CSS\Post( $template_id );
		$css_file->enqueue();

	}

	/**
	 * Register Elementor-related dynamic tags
	 *
	 * @param  [type] $dynamic_tags [description]
	 * @param  [type] $tags_module [description]
	 * @return [type]              [description]
	 */
	public function register_dynamic_tags( $dynamic_tags, $tags_module ) {

		require_once jet_engine()->modules->modules_path( 'profile-builder/inc/dynamic-tags/profile-page-url.php' );

		$tags_module->register_tag( $dynamic_tags, new Dynamic_Tags\Profile_Page_URL() );

	}

	/**
	 * Add account URL into the link options Dynamic Image widget
	 * @param  [type] $widget [description]
	 * @return [type]         [description]
	 */
	public function register_img_link_controls( $widget ) {
		$this->register_link_controls( $widget, true );
	}

	/**
	 * Register link control
	 *
	 * @param  [type] $widget [description]
	 * @return [type]         [description]
	 */
	public function register_link_controls( $widget = null, $is_image = false ) {

		$pages = $this->get_pages_for_options( 'elementor' );

		$condition = array(
			'dynamic_link_source' => 'profile_page',
		);

		if ( $is_image ) {
			$condition = array(
				'linked_image'      => 'yes',
				'image_link_source' => 'profile_page',
			);
		}

		$widget->add_control(
			'dynamic_link_profile_page',
			array(
				'label'     => __( 'Profile Page', 'jet-engine' ),
				'type'      => 'select',
				'default'   => '',
				'groups'    => $pages,
				'condition' => $condition,
			)
		);

	}

	/**
	 * Register profile builder widgets
	 *
	 * @return void
	 */
	public function register_widgets( $widgets_manager, $elementor_views ) {

		$elementor_views->register_widget(
			jet_engine()->modules->modules_path( 'profile-builder/inc/widgets/profile-menu-widget.php' ),
			$widgets_manager,
			__NAMESPACE__ . '\Profile_Menu_Widget'
		);

		$template_mode = Module::instance()->settings->get( 'template_mode' );

		if ( 'content' === $template_mode ) {
			$elementor_views->register_widget(
				jet_engine()->modules->modules_path( 'profile-builder/inc/widgets/profile-content-widget.php' ),
				$widgets_manager,
				__NAMESPACE__ . '\Profile_Content_Widget'
			);
		}

	}

}
