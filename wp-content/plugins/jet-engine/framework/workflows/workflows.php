<?php
/**
 * Workflows manager module
 *
 * Version: 1.0.0
 */
namespace Croblock\Workflows;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Manager {

	public $namespace   = false;
	public $label       = false;
	public $path        = false;
	public $url         = false;
	public $parent_page = false;
	public $page_name   = null;
	public $page_slug   = 'crocoblock-workflows';

	public $version = '1.0.0';

	private static $page_registered = [];

	protected $storage      = null;
	protected $state        = null;
	protected $remote_api   = null;
	protected $ui           = null;
	protected $dependencies = null;
	protected $prefix       = 'crocoblock';

	public function __construct( $args = [] ) {

		$this->prefix      = ! empty( $args['prefix'] ) ? $this->prepared_prefix( $args['prefix'] ) : $this->prefix;
		$this->namespace   = ! empty( $args['namespace'] ) ? $args['namespace'] : '';
		$this->label       = ! empty( $args['label'] ) ? $args['label'] : '';
		$this->path        = ! empty( $args['path'] ) ? untrailingslashit( $args['path'] ) : '';
		$this->url         = ! empty( $args['url'] ) ? untrailingslashit( $args['url'] ) : '';
		$this->parent_page = ! empty( $args['parent_page'] ) ? $args['parent_page'] : '';
		$this->page_slug   = ! empty( $args['page_slug'] ) ? $args['page_slug'] : $this->page_slug;
		$this->page_name   = ! empty( $args['page_name'] ) ? $args['page_name'] : esc_html__( 'Interactive Tutorials', 'jet-engine' );

		$this->storage()->add_namespace( $this->namespace, $this->label );

		if ( $this->state()->get_active_workflow() ) {
			add_action( 'admin_enqueue_scripts', [ $this->ui(), 'assets' ] );
		}

		// Instintiate dependencies manager to attach AJAX hooks
		$this->dependencies();

	}

	/**
	 * Lightly ensure correct prefix will be used
	 * 
	 * @param  string $prefix [description]
	 * @return [type]         [description]
	 */
	public function prepared_prefix( $prefix = '' ) {
		return str_replace( [ ' ', '-' ], '_', $prefix );
	}

	/**
	 * Run workflows UI and execution
	 * 
	 * @return [type] [description]
	 */
	public function run() {
		// register UI
		add_action( 'admin_menu', [ $this, 'register_page' ] );
	}

	/**
	 * Returns prefix of current Workflows instance.
	 * 
	 * @return [type] [description]
	 */
	public function prefix() {
		return $this->prefix;
	}

	/**
	 * Register admin page for all workflows instances
	 * 
	 * @return [type] [description]
	 */
	public function register_page() {

		if ( in_array( $this->parent_page . $this->page_slug, self::$page_registered ) ) {
			return;
		}

		add_submenu_page(
			$this->parent_page,
			$this->page_name,
			$this->page_name,
			'manage_options',
			$this->page_slug,
			[ $this->ui(), 'render_page' ]
		);

		self::$page_registered[] = $this->parent_page . $this->page_slug;

	}

	/**
	 * Returns workflows page URL
	 * 
	 * @return [type] [description]
	 */
	public function get_page_url() {
		return add_query_arg( [ 'page' => $this->page_slug ], admin_url( 'admin.php' ) );
	}

	/**
	 * Return an instance of Remote API manager
	 * 
	 * @return [type] [description]
	 */
	public function remote_api() {

		if ( ! $this->remote_api ) {
			
			if ( ! class_exists( __NAMESPACE__ . '\\Remote_API' ) ) {
				require_once $this->path . '/includes/remote-api.php';
			}

			$this->remote_api = new Remote_API( $this );
		}

		return $this->remote_api;

	}

	/**
	 * Returns and instance of workflows storage
	 * 
	 * @return [type] [description]
	 */
	public function storage() {

		if ( ! $this->storage ) {
			
			if ( ! class_exists( __NAMESPACE__ . '\\Storage' ) ) {
				require_once $this->path . '/includes/storage.php';
			}

			$this->storage = new Storage( $this );
		}

		return $this->storage;

	}

	/**
	 * Returns and instance of workflows storage
	 * 
	 * @return [type] [description]
	 */
	public function state() {

		if ( ! $this->state ) {
			
			if ( ! class_exists( __NAMESPACE__ . '\\State' ) ) {
				require_once $this->path . '/includes/state.php';
			}

			$this->state = State::instance();
		}

		return $this->state->with_context( $this );

	}

	/**
	 * Returns and instance of workflows storage
	 * 
	 * @return [type] [description]
	 */
	public function dependencies() {

		if ( ! $this->dependencies ) {
			
			if ( ! class_exists( __NAMESPACE__ . '\\Dependencies' ) ) {
				require_once $this->path . '/includes/dependencies.php';
			}

			$this->dependencies = Dependencies::instance();
		}

		return $this->dependencies->with_context( $this );

	}

	/**
	 * Return an instance of UI manager
	 * 
	 * @return [type] [description]
	 */
	public function ui() {

		if ( ! $this->ui ) {
			
			if ( ! class_exists( __NAMESPACE__ . '\\UI' ) ) {
				require_once $this->path . '/includes/ui.php';
			}

			$this->ui = new UI( $this );
		}

		return $this->ui;

	}

}
