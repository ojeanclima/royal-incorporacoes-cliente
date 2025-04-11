<?php


namespace JFB_PDF_Modules\Templates\PostType;

use JFB_PDF\Vendor\Auryn\ConfigException;
use JFB_PDF\Vendor\Auryn\Injector;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

class PostType {

	const SLUG      = 'jet-block-template';
	const MENU_SLUG = 'edit.php?post_type=' . self::SLUG;

	const CAPABILITIES = array(
		'edit_jet_bl_tmpl',
		'read_jet_bl_tmpl',
		'delete_jet_bl_tmpl',
		'edit_jet_bl_tmpls',
		'edit_others_jet_bl_tmpls',
		'delete_jet_bl_tmpls',
		'publish_jet_bl_tmpls',
		'read_private_jet_bl_tmpls',
	);

	private $capability = 'manage_options';

	public function init_hooks() {
		add_action( 'init', array( $this, 'register' ) );
		// watch on this methods performance because it executed multiple times on each page load
		add_filter( 'user_has_cap', array( $this, 'add_admin_capabilities' ) );
		add_filter( 'gutenberg_can_edit_post_type', array( $this, 'can_edit_post_type' ), 150, 2 );
		add_filter( 'use_block_editor_for_post_type', array( $this, 'can_edit_post_type' ), 150, 2 );
		add_action( 'admin_menu', array( $this, 'register_submenu_item_for_jfb' ) );
	}

	public function register() {
		register_post_type(
			self::SLUG,
			array(
				'labels'                => array(
					'name'               => __( 'Templates', 'jet-form-builder-pdf' ),
					'all_items'          => __( 'Templates', 'jet-form-builder-pdf' ),
					'add_new'            => __( 'Add New', 'jet-form-builder-pdf' ),
					'add_new_item'       => __( 'Add New Template', 'jet-form-builder-pdf' ),
					'edit_item'          => __( 'Edit Template', 'jet-form-builder-pdf' ),
					'new_item'           => __( 'New Template', 'jet-form-builder-pdf' ),
					'view_item'          => __( 'View Template', 'jet-form-builder-pdf' ),
					'search_items'       => __( 'Search Template', 'jet-form-builder-pdf' ),
					'not_found'          => __( 'No Templates Found', 'jet-form-builder-pdf' ),
					'not_found_in_trash' => __( 'No Templates Found In Trash', 'jet-form-builder-pdf' ),
					'singular_name'      => __( 'JetTemplate', 'jet-form-builder-pdf' ),
					'menu_name'          => __( 'JetTemplate', 'jet-form-builder-pdf' ),
				),
				'public'                => true,
				'show_ui'               => true,
				'show_in_admin_bar'     => true,
				'show_in_menu'          => false,
				'show_in_nav_menus'     => false,
				'show_in_rest'          => true,
				'rest_controller_class' => RestController::class,
				'publicly_queryable'    => false,
				'exclude_from_search'   => true,
				'has_archive'           => false,
				'query_var'             => false,
				'can_export'            => true,
				'rewrite'               => false,
				'capability_type'       => 'jet_bl_tmpl',
				'menu_icon'             => $this->get_post_type_icon(),
				'menu_position'         => 120,
				'supports'              => array(
					'title',
					'editor',
					'author',
					'revisions',
					'custom-fields',
				),
				'template'              => array(
					array(
						'core/paragraph',
						array(
							'content' => __(
								'Here you can specify [field label]: %[field name]% (remove square brackets after filling)',
								'jet-form-builder-pdf'
							),
						),
					),
				),
			)
		);
	}

	public function register_submenu_item_for_jfb() {
		add_submenu_page(
			'edit.php?post_type=jet-form-builder',
			__( 'JetTemplates', 'jet-form-builder-pdf' ),
			__( 'Templates', 'jet-form-builder-pdf' ),
			'publish_jet_bl_tmpls',
			self::MENU_SLUG
		);
	}

	public function add_admin_capabilities( $allcaps ) {
		if ( empty( $allcaps[ $this->capability ] ) ) {
			return $allcaps;
		}

		foreach ( self::CAPABILITIES as $capability ) {
			$allcaps[ $capability ] = true;
		}

		return $allcaps;
	}

	/**
	 * @param $can
	 * @param $post_type
	 *
	 * @return bool
	 */
	public function can_edit_post_type( $can, $post_type ): bool {
		return self::SLUG === $post_type ? true : $can;
	}

	private function get_post_type_icon(): string {
		return include_once __DIR__ . '/icon.php';
	}

	/**
	 * @param string $capability
	 */
	public function set_capability( string $capability ) {
		$this->capability = $capability;
	}

}
