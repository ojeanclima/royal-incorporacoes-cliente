<?php
/**
 * Class description
 *
 * @package   package_name
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Listings_Post_Type' ) ) {

	/**
	 * Define Jet_Engine_Listings_Post_Type class
	 */
	class Jet_Engine_Listings_Post_Type {

		/**
		 * Post type slug.
		 *
		 * @var string
		 */
		public $post_type = 'jet-engine';

		public $admin_screen = null;

		private $nonce_action = 'jet-engine-listings';

		/**
		 * Constructor for the class
		 */
		public function __construct() {

			add_action( 'init', array( $this, 'register_post_type' ) );

			if ( ! empty( $_GET['elementor-preview'] ) ) {
				add_action( 'template_include', array( $this, 'set_editor_template' ), 9999 );
			}

			if ( is_admin() ) {
				
				add_action( 'admin_menu', array( $this, 'add_templates_page' ), 20 );
				add_action( 'add_meta_boxes_' . $this->slug(), array( $this, 'disable_metaboxes' ), 9999 );
				add_action( 'admin_enqueue_scripts', array( $this, 'listings_page_assets' ) );

				add_filter( 'post_row_actions', array( $this, 'remove_view_action' ), 10, 2 );

			}

			require_once jet_engine()->plugin_path( 'includes/components/listings/admin-screen.php' );
			$this->admin_screen = new Jet_Engine_Listing_Admin_Screen( $this->slug() );

			add_action( 'wp', array( $this, 'set_singular_preview_object' ) );
			
			add_filter( 
				'jet-engine/profile-builder/create-template/' . $this->slug(),
				[ $this, 'create_profile_template' ],
				10, 3
			);

		}

		/**
		 * Create new profile template
		 * 
		 * @param  array  $result         Argument to set URL and ID into.
		 * @param  string $template_name Name of template to create.
		 * @param  string $template_view Listing view.
		 * @return string
		 */
		public function create_profile_template( $result = [], $template_name = '', $template_view = '' ) {

			if ( ! $template_name || ! $template_view ) {
				return $result;
			}

			$source  = 'users';
			$listing = [
				'source'    => $source,
				'post_type' => 'post',
				'tax'       => 'category',
			];

			$template_id = $this->admin_screen->update_template( [
				'post_title' => $template_name,
				'post_type'   => $this->slug(),
				'post_status' => 'publish',
				'meta_input' => [
					'_listing_data' => $listing,
					'_listing_type' => $template_view,
					'_elementor_page_settings' => [
						'listing_source' => $source,
						'listing_post_type' => 'post',
						'listing_tax' => 'category',
						'repeater_source' => '',
						'repeater_field' => '',
						'repeater_option' => '',
					],
				],
			], $template_view );

			if ( ! $template_id ) {
				return $result;
			}

			return [
				'template_url' => $this->admin_screen->get_edit_url( $template_view, $template_id ),
				'template_id'  => $template_id,
			];

		}

		/**
		 * Setup correct preview object when opening listing item on the front-end directly
		 * Required for correct rendering of the editor mode for some builders or for public preview
		 */
		public function set_singular_preview_object() {
			if ( is_singular( $this->slug() ) ) {
				// Setup preview instance for current listing
				$preview = new Jet_Engine_Listings_Preview( array(), get_the_ID() );
				// Store preview object as root insted of listing item WP_Post object
				jet_engine()->listings->objects_stack->set_root_object( $preview->get_preview_object() );
				// Avoid JetEngine from trying to set current object by itself (causing reseting of current object)
				remove_action( 'the_post', array( jet_engine()->listings->data, 'maybe_set_current_object' ), 10, 2 );
			}
		}

		/**
		 * Actions posts
		 *
		 * @param  [type] $actions [description]
		 * @param  [type] $post    [description]
		 * @return [type]          [description]
		 */
		public function remove_view_action( $actions, $post ) {

			if ( $this->slug() === $post->post_type ) {
				unset( $actions['view'] );
			}

			return $actions;

		}

		/**
		 * Assets related to create new listing/component form
		 * 
		 * @param  boolean $force_print_templates [description]
		 * @param  array   $vars                  [description]
		 * @return [type]                         [description]
		 */
		public function listing_form_assets( $force_print_templates = false, $vars = array() ) {

			jet_engine()->register_jet_plugins_js();

			/**
			 * Hook fires also in Elemntor editor for inline create listing in Listing Grid widget.
			 * So be careful with enqueuing additional assets on this hook.
			 * @see https://github.com/Crocoblock/issues-tracker/issues/13481
			 */
			do_action( 'jet-engine/templates/before-listing-assets' );

			wp_enqueue_script(
				'jet-listings-form',
				jet_engine()->plugin_url( 'assets/js/admin/listings-popup.js' ),
				array( 'jquery', 'jet-plugins' ),
				jet_engine()->get_version(),
				true
			);

			wp_localize_script( 'jet-listings-form', 'JetListingsSettings', apply_filters(
				'jet-engine/templates/localized-settings',
				array_merge( array(
					'hasElementor' => jet_engine()->has_elementor(),
					'exclude'      => array(),
					'defaults'     => array(),
					'_nonce'       => wp_create_nonce( $this->nonce_action ),
				), $vars )
			) );

			wp_enqueue_style(
				'jet-listings-form',
				jet_engine()->plugin_url( 'assets/css/admin/listings.css' ),
				array(),
				jet_engine()->get_version()
			);

			if ( $force_print_templates ) {
				$this->print_listings_popup();
			} else {
				add_action( 'admin_footer', array( $this, 'print_listings_popup' ), 999 );
			}
			
		}

		/**
		 * Check if we currently on the Listing Items list page.
		 *
		 * @return boolean
		 */
		public function is_listings_edit_page() {

			$screen = get_current_screen();

			if ( $screen && $screen->id === 'edit-' . $this->slug() ) {
				return true;
			} elseif ( $screen && $screen->id !== 'edit-' . $this->slug() ) {
				return false;
			}

			// Catch the case when screen is not set yet
			$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : false;

			if ( $request_uri
				&& false !== strpos( $request_uri, 'edit.php' )
				&& ! empty( $_GET['post_type'] )
				&& $this->slug() === $_GET['post_type']
			) {
				return true;
			} else {
				return false;
			}
		}

		public function get_nonce_action() {
			return $this->nonce_action;
		}

		public function listings_page_assets() {

			if ( ! $this->is_listings_edit_page() ) {
				return;
			}

			$this->listing_form_assets();

			jet_engine()->get_video_help_popup( array(
				'popup_title' => __( 'What is Listing Grid?', 'jet-engine' ),
				'embed' => 'https://www.youtube.com/embed/JxvtMzwHGIw',
			) )->wp_page_popup();

		}

		/**
		 * Returns available listing sources list
		 *
		 * @return [type] [description]
		 */
		public function get_listing_item_sources() {
			return apply_filters( 'jet-engine/templates/listing-sources', array(
				'posts'    => __( 'Posts', 'jet-engine' ),
				'query'    => __( 'Query Builder', 'jet-engine' ),
				'terms'    => __( 'Terms', 'jet-engine' ),
				'users'    => __( 'Users', 'jet-engine' ),
				'repeater' => __( 'Repeater Field', 'jet-engine' ),
			) );
		}

		public function get_listing_views() {
			return apply_filters( 'jet-engine/templates/listing-views', array() );
		}

		/**
		 * Print template type form HTML
		 *
		 * @return void
		 */
		public function print_listings_popup() {
			echo $this->admin_screen->get_listing_popup();
		}

		/**
		 * Templates post type slug
		 *
		 * @return string
		 */
		public function slug() {
			return $this->post_type;
		}

		/**
		 * Disable metaboxes from Jet Templates
		 *
		 * @return void
		 */
		public function disable_metaboxes() {
			global $wp_meta_boxes;
			unset( $wp_meta_boxes[ $this->slug() ]['side']['core']['pageparentdiv'] );
		}

		/**
		 * Register templates post type
		 *
		 * @return void
		 */
		public function register_post_type() {

			$args = array(
				'labels' => array(
					'name'               => esc_html__( 'Listing Items/Components', 'jet-engine' ),
					'singular_name'      => esc_html__( 'Listing Item/Components', 'jet-engine' ),
					'add_new'            => esc_html__( 'Add New Listing Item', 'jet-engine' ),
					'add_new_item'       => esc_html__( 'Add New Item', 'jet-engine' ),
					'edit_item'          => esc_html__( 'Edit Item', 'jet-engine' ),
					'new_item'           => esc_html__( 'Add New Item', 'jet-engine' ),
					'view_item'          => esc_html__( 'View Item', 'jet-engine' ),
					'search_items'       => esc_html__( 'Search Item', 'jet-engine' ),
					'not_found'          => esc_html__( 'No Templates Found', 'jet-engine' ),
					'not_found_in_trash' => esc_html__( 'No Templates Found In Trash', 'jet-engine' ),
					'menu_name'          => esc_html__( 'My Library', 'jet-engine' ),
				),
				'public'              => false,
				'hierarchical'        => false,
				'show_ui'             => true,
				'show_in_menu'        => false,
				'show_in_nav_menus'   => false,
				'show_in_rest'        => true,
				'can_export'          => true,
				'exclude_from_search' => true,
				'capability_type'     => 'post',
				'rewrite'             => false,
				'supports'            => array( 
					'title', 'editor', /*'thumbnail',*/ 'author', 'elementor', 'custom-fields'
				),
			);

			if ( current_user_can( 'edit_posts' ) ) {
				$args['public'] = true;
			}

			register_post_type(
				$this->slug(),
				apply_filters( 'jet-engine/templates/post-type/args', $args )
			);

		}

		/**
		 * Menu page
		 */
		public function add_templates_page() {

			$views = $this->get_listing_views();

			if ( empty( $views ) ) {
				return;
			}

			add_submenu_page(
				jet_engine()->admin_page,
				esc_html__( 'Listings/Components', 'jet-engine' ),
				esc_html__( 'Listings/Components', 'jet-engine' ),
				'manage_options',
				'edit.php?post_type=' . $this->slug()
			);

		}

		/**
		 * Editor templates.
		 *
		 * @param  string $template Current template name.
		 * @return string
		 */
		public function set_editor_template( $template ) {

			$found = false;

			if ( is_singular( $this->slug() ) ) {
				$found    = true;
				$template = jet_engine()->plugin_path( 'templates/blank.php' );
			}

			if ( $found ) {
				do_action( 'jet-engine/post-type/editor-template/found' );
			}

			return $template;

		}

	}

}
