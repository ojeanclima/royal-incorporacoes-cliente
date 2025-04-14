<?php
namespace Jet_Engine_Dynamic_Tables\Admin;

class Dashboard extends \Jet_Engine_Base_WP_Intance{

	/**
	 * Base slug for CPT-related pages
	 * @var string
	 */
	public $page = 'jet-engine-tables';

	/**
	 * Action request key
	 *
	 * @var string
	 */
	public $action_key = 'table_action';

	/**
	 * Metaboxes to register
	 *
	 * @var array
	 */
	public $meta_boxes = array();

	/**
	 * Set object type
	 * @var string
	 */
	public $object_type = 'table';

	/**
	 * Instance.
	 *
	 * Holds query builder instance.
	 *
	 * @access public
	 * @static
	 *
	 * @var Plugin
	 */
	public static $instance = null;

	/**
	 * Instance.
	 *
	 * Ensures only one instance of the plugin class is loaded or can be loaded.
	 *
	 * @access public
	 * @static
	 *
	 * @return Plugin An instance of the class.
	 */
	public static function instance() {

		if ( is_null( self::$instance ) ) {

			self::$instance = new self();

		}

		return self::$instance;

	}

	/**
	 * Constructor for the class
	 */
	function __construct() {

		new API_Handler( $this->instance_slug() );

		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'add_menu_page' ), 20 );
		}

		if ( ! $this->is_cpt_page() ) {
			return;
		}

		add_action( 'admin_init', array( $this, 'register_pages' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ), 0 );
		add_action( 'admin_init', array( $this, 'handle_actions' ) );

	}

	/**
	 * Return admin pages for current instance
	 *
	 * @return array
	 */
	public function get_instance_pages() {

		return array(
			new Pages\Tables_List( $this ),
			new Pages\Edit( $this ),
		);

	}

	/**
	 * Init data instance
	 *
	 * @return [type] [description]
	 */
	public function init_data() {
		// not used
	}

	/**
	 * Register current object instances
	 *
	 * @return void
	 */
	public function register_instances() {
		// not used
	}

	/**
	 * Returns current menu page title (for JetEngine submenu)
	 * @return [type] [description]
	 */
	public function get_page_title() {
		return __( 'Tables Builder', 'jet-engine' );
	}

	/**
	 * Returns current instance slug
	 *
	 * @return [type] [description]
	 */
	public function instance_slug() {
		return $this->object_type;
	}

	/**
	 * Returns default config for add/edit page
	 *
	 * @param  array  $config [description]
	 * @return [type]         [description]
	 */
	public function get_admin_page_config( $config = array() ) {

		$default_settings = array(
			'type'  => 'text',
			'width' => '100%',
		);

		$default = array(
			'api_path_edit'       => '', // Set individually for apropriate page
			'api_path_get'        => jet_engine()->api->get_route( 'get-item' ),
			'instance'            => $this->instance_slug(),
			'edit_button_label'   => '', // Set individually for apropriate page,
			'item_id'             => false,
			'redirect'            => '', // Set individually for apropriate page,
			'general_settings'    => array(),
			'notices'             => array(
				'name'    => __( 'Please, set table name', 'jet-engine' ),
				'success' => __( 'Table settings are updated', 'jet-engine' ),
				'copied'  => __( 'Copied!', 'jet-engine' ),
			),
		);

		return array_merge( $default, $config );

	}

}
