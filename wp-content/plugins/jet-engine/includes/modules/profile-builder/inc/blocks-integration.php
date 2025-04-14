<?php
namespace Jet_Engine\Modules\Profile_Builder;

class Blocks_Integration extends Base_Integration {

	/**
	 * Constructor for the class
	 */
	public function __construct() {
		add_filter( 'jet-engine/blocks-views/editor/config', array( $this, 'register_pages_options' ), 10 );
		add_filter( 'jet-engine/profile-builder/template/content', array( $this, 'render_template_content' ), 10, 4 );
		add_action( 'jet-engine/blocks-views/register-block-types', array( $this, 'register_block_types' ) );
		add_action( 'jet-engine/blocks-views/editor-script/before', array( $this, 'enqueue_block_assets' ) );
		add_filter( 'jet-engine/blocks-views/block-types/attributes/dynamic-image', array( $this, 'add_dynamic_image_block_attrs') );
	}

	/**
	 * Render template content
	 *
	 * @param  [type] $content     [description]
	 * @param  [type] $template_id [description]
	 * @return [type]              [description]
	 */
	public function render_template_content( $content, $template_id, $frontend, $template ) {
		
		if ( jet_engine()->listings->post_type->slug() !== $template->post_type ) {
			return $content;
		}

		jet_engine()->frontend->set_listing( $template_id );
		return jet_engine()->frontend->get_listing_item_content( $template_id );

	}

	/**
	 * Profile blocks JS
	 *
	 * @return [type] [description]
	 */
	public function enqueue_block_assets() {

		wp_enqueue_script(
			'jet-engine-profile-blocks',
			jet_engine()->plugin_url( 'includes/modules/profile-builder/inc/assets/js/blocks.js' ),
			array( 'wp-components', 'wp-element', 'wp-blocks', 'wp-block-editor', 'lodash' ),
			jet_engine()->get_version(),
			true
		);

		wp_localize_script( 'jet-engine-profile-blocks', 'JetEngineProfileBlocksConfig', array(
			'account_roles' => Module::instance()->frontend->menu->get_available_menu_roles( 'account_page', true ),
			'user_roles'    => Module::instance()->frontend->menu->get_available_menu_roles( 'user_page', true ),
		) );

	}

	/**
	 * Register profile related block types
	 *
	 * @param  [type] $manager [description]
	 * @return [type]          [description]
	 */
	public function register_block_types( $manager ) {

		require Module::instance()->module_path( 'blocks/profile-content.php' );
		require Module::instance()->module_path( 'blocks/profile-menu.php' );

		$manager->register_block_type( new Blocks\Profile_Content() );
		$manager->register_block_type( new Blocks\Profile_Menu() );

	}

	/**
	 * Register options for select profile builder pages control
	 */
	public function register_pages_options( $config ) {

		$pages = array_map( function ( $page ) {

			if ( ! empty( $page['options'] ) ) {
				$page['values'] = $page['options'];
				unset( $page['options'] );
			}

			return $page;

		}, $this->get_pages_for_options( 'blocks' ) );

		if ( ! empty( $pages ) ) {
			$config['profileBuilderPages'] = $pages;
		}

		return $config;

	}

	public function add_dynamic_image_block_attrs( $attrs ) {

		$attrs['dynamic_link_profile_page'] = array(
			'type'    => 'string',
			'default' => '',
		);

		return $attrs;
	}

}
