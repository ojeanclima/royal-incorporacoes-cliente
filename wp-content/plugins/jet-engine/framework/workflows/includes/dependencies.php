<?php
/**
 * Dependencies manager manager
 */
namespace Croblock\Workflows;

class Dependencies {

	/**
	 * A reference to an instance of this class.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    Module
	 */
	private static $instance = null;

	/**
	 * @var Conditions\Manager
	 */
	public $workflows = null;

	private $nonce_key = 'crocoblock-workflows-dependencies';

	/**
	 * Constructor for the class
	 */
	public function __construct() {
		add_action( 'wp_ajax_crocoblock_workflow_process_dependency', [ $this, 'process_ajax_dependency' ] );
	}

	/**
	 * Return instance oif state manager with provided workflows parent
	 * @param  [type] $workflows [description]
	 * @return [type]            [description]
	 */
	public function with_context( $workflows ) {
		$this->workflows = $workflows;
		return $this;
	}

	/**
	 * Return given workflow with the result of dependencies checking
	 * 
	 * @param [type] $workflow [description]
	 */
	public function add_checked_dependencies( $workflow ) {

		foreach ( $workflow['steps'] as $s_index => $step ) {

			if ( ! empty( $step['dependency'] ) 
				&& ! empty( $step['dependency']['type'] ) 
			) {
				$step['dependency']['isCompleted'] = $this->check_dependency(
					$step['dependency']['type'],
					$step['dependency']
				);

				$workflow['steps'][ $s_index ] = $step;
			}

		}

		return $workflow;
	}

	/**
	 * Ajax callback to process state update
	 * @return [type] [description]
	 */
	public function process_ajax_dependency() {
		
		$nonce = ! empty( $_REQUEST['nonce'] ) ? $_REQUEST['nonce'] : false;

		if ( ! $nonce || ! wp_verify_nonce( $nonce, $this->nonce_key ) ) {
			wp_send_json_error( __( 'The page is expired. Please reload page and try again', 'jet-engine' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Access denied', 'jet-engine' ) );
		}

		$data = ! empty( $_REQUEST['data'] ) ? $_REQUEST['data'] : [];
		$type = $data['type'] ?? false;

		if ( ! $type ) {
			wp_send_json_error( __( 'Incomplete request', 'jet-engine' ) );
		}
		
		try {
			$this->process_dependency_by_type( $type, $data );
			wp_send_json_success();
		} catch ( \Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}

	}

	/**
	 * Check if given dependency completed
	 * 
	 * @param  [type] $type [description]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function check_dependency( $type, $data ) {

		switch ( $type ) {

			case 'plugin':

				$file = $data['file'] ?? false;

				if ( $file && function_exists( 'is_plugin_active' ) && is_plugin_active( $file ) ) {
					return true;
				} else {
					return false;
				}

			case 'option':

				$options = $data['options'] ?? [];
				$result  = true;

				foreach ( $options as $option => $value ) {

					$option = explode( '/', $option );
					$base = $option[0];
					unset( $option[0] );

					$current_option = get_option( $base );

					if ( empty( $option ) ) {
						if ( $current_option != $value ) {
							$result = false;
						}
					} else {

						$current_option = ! empty( $current_option ) ? $current_option : [];

						foreach ( $option as $key ) {
							$current_option = $current_option[ $key ] ?? [];
						}

						if ( $current_option != $value ) {
							$result = false;
						}

					}

				}

				return $result;

			case 'jet_engine_module':
			case 'jet_engine_module_external':
				$module = $data['module'] ?? false;
				return $module && jet_engine()->modules->is_module_active( $module ) ? true : false;
		}

		return false;
	}

	/**
	 * Process dependcy completion of given type
	 * 
	 * @param  [type] $type [description]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function process_dependency_by_type( $type, $data ) {
		
		if ( $this->check_dependency( $type, $data ) ) {
			return true;
		}

		switch ( $type ) {

			case 'plugin':

				$slug   = $data['slug'] ?? false;
				$file   = $data['file'] ?? false;
				$source = $data['source'] ?? 'wp';

				if ( ! $file || ! $slug ) {
					throw new \Exception( __( 'Plugin slug or filename is missing in request', 'jet-engine' ) );
				}

				$result = $this->install_plugin( $source, [ 'file' => $file, 'slug' => $slug ] );

				if ( $result ) {
					return;
				} else {
					throw new \Exception( __( 'Plugin could not be installed automatically. Please install it manually and continue workflow.', 'jet-engine' ) );
				}

				break;

			case 'option':
				
				$options = $data['options'] ?? [];

				foreach ( $options as $option => $value ) {

					$option = explode( '/', $option );
					$base = $option[0];
					unset( $option[0] );

					$current_option = get_option( $base );

					if ( empty( $option ) ) {
						$new_value = $value;
					} else {

						$current_option = ! empty( $current_option ) ? $current_option : [];
						$new_value      = $this->recursive_update_option( $current_option, $option, $value );

					}

					update_option( $base, $new_value );

				}

				return true;

			case 'jet_engine_module':

				$module = $data['module'] ?? false;

				if ( ! $module ) {
					throw new \Exception( __( 'Module slug not found in request.', 'jet-engine' ) );
				}

				jet_engine()->modules->activate_module( $module );
				return true;

			case 'jet_engine_module_external':

				$module      = $data['module'] ?? false;
				$plugin_file = $data['file'] ?? false;

				if ( ! $module || ! $plugin_file ) {
					throw new \Exception( __( 'Module slug or filename not found in request.', 'jet-engine' ) );
				}

				jet_engine()->modules->installer->install_and_activate_module( $module, $plugin_file );
				return true;
		}

		throw new \Exception( __( 'Unknown dependency type - ' . $type, 'jet-engine' ) );

	}

	public function recursive_update_option( $option_value, $props_chain, $value ) {

		if ( ! $option_value ) {
			$option_value = [];
		}

		$props_chain  = array_values( $props_chain );
		$current_prop = $props_chain[0];

		unset( $props_chain[0] );

		if ( empty( $props_chain ) ) {

			$current_value = $option_value[ $current_prop ] ?? false;

			if ( is_array( $value ) ) {
				if ( empty( $current_value ) || ! is_array( $current_value ) ) {
					$current_value = $value;
				} else {
					$current_value = array_merge( $current_value, $value );
				}
			} else {
				$current_value = $value;
			}

			$option_value[ $current_prop ] = $current_value;

			return $option_value;

		} else {

			$prop_value = $option_value[ $current_prop ] ?? [];
			$new_value = $this->recursive_update_option( $prop_value, $props_chain, $value );
			$option_value[ $current_prop ] = $new_value;

			return $option_value;
		}

	}

	/**
	 * Install plugin by slug and source
	 * 
	 * @param  string $slug   [description]
	 * @param  string $source [description]
	 * @return [type]         [description]
	 */
	public function install_plugin( $source = '', $data = [] ) {

		$status = array();

		if ( ! current_user_can( 'install_plugins' ) ) {
			throw new \Exception( __( 'You are not allowed to install plugins on this site', 'jet-engine' ) );
		}

		$package = false;
		$slug    = $data['slug'];
		$file    = $data['file'];

		switch ( $source ) {
			
			case 'wordpress':

				if ( ! function_exists( 'plugins_api' ) ) {
					require_once ABSPATH . 'wp-admin/includes/plugin-install.php'; // Need for plugins_api
				}

				$api = plugins_api(
					'plugin_information',
					array( 'slug' => $slug, 'fields' => array( 'sections' => false ) )
				);

				if ( is_wp_error( $api ) ) {
					throw new \Exception( __( 'Plugins API error', 'jet-engine' ) );
				}

				if ( isset( $api->download_link ) ) {
					$package = $api->download_link;
				}

				break;

			case 'crocoblock':

				if ( ! class_exists( '\Jet_Dashboard\Utils' ) ) {
					throw new \Exception(
						__( 'Dashboard module is required to install Crocoblock dependencies', 'jet-engine' ) 
					);
				}

				$package = \Jet_Dashboard\Utils::package_url( $file );

				if ( ! $package ) {
					throw new \Exception(
						__( 'Crocoblock plugins can`t be installed without a license', 'jet-engine' ) 
					);
				}

				break;

			default:

				$package = $slug;
				break;
		}

		include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );

		$skin     = new \WP_Ajax_Upgrader_Skin();
		$upgrader = new \Plugin_Upgrader( $skin );
		$result   = $upgrader->install( $package );

		if ( is_wp_error( $result ) ) {
			throw new \Exception( $result->get_error_message() );
		} elseif ( is_wp_error( $skin->result ) ) {
			if ( 'folder_exists' !== $skin->result->get_error_code() ) {
				throw new \Exception( $skin->result->get_error_message() );
			}
		} elseif ( $skin->get_errors()->get_error_code() ) {
			throw new \Exception( $skin->get_error_messages() );
		} elseif ( is_null( $result ) ) {
			global $wp_filesystem;

			// Pass through the error from WP_Filesystem if one was raised.
			if ( $wp_filesystem instanceof \WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) {
				throw new \Exception( $skin->get_error_messages() );
			}

		}

		$result = activate_plugin( $file );

		if ( is_wp_error( $result ) ) {
			throw new \Exception( $result->get_error_messages() );
		}

		return true;

	}

	/**
	 * Returns a nonce for states
	 * @return [type] [description]
	 */
	public function nonce() {
		return wp_create_nonce( $this->nonce_key );
	}

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return Module
	 */
	public static function instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

}
