<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'JFB_License_Manager' ) ) {

	/**
	 * Class JFB_License_Manager
	 */
	class JFB_License_Manager {

		/**
		 * @var self
		 */
		private static $instance;

		/**
		 * [$api_url description]
		 *
		 * @var string
		 */
		public $api_url = 'https://account.jetformbuilder.com';

		/**
		 * [$license_data_key description]
		 *
		 * @var string
		 */
		public $license_data_key = 'jfb-license-data';

		/**
		 * [$settings description]
		 *
		 * @var null
		 */
		public $license_data = null;


		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public static function clear() {
			self::$instance = null;
		}

		/**
		 * Proccesing subscribe form ajax
		 *
		 * @return void
		 */
		public function license_action() {
			$data = $this->resolve_request();

			$license_action = $data['action'];
			$license_key    = $data['license'];

			if ( empty( $license_key ) ) {
				$license_key = $this->get_license_key();
			}

			$responce_data = $this->license_action_query( $license_action, $license_key );

			if ( ! $responce_data['success'] ) {

				if ( 'invalid' === $responce_data['license'] ) {
					wp_send_json(
						array(
							'success' => false,
							'message' => __( 'Invalid license key', 'jet-form-builder' ),
							'data'    => array(),
						)
					);
				}

				if ( 'failed' === $responce_data['license'] ) {

					$license_list = $this->get_license_data();

					$license_list = array_filter(
						$license_list,
						function ( $license_data ) use ( $license_key ) {
							return $license_data['license_key'] !== $license_key;
						}
					);

					update_option( $this->license_data_key, $license_list );

					wp_send_json(
						array(
							'success' => true,
							'message' => __( 'The license for this site is already activated', 'jet-form-builder' ),
							'data'    => array(),
						)
					);
				}

				wp_send_json(
					array(
						'success' => false,
						'message' => __( 'Server error. Please, try again later', 'jet-form-builder' ),
						'data'    => array(),
					)
				);
			}

			switch ( $license_action ) {
				case 'activate_license':
					$this->add_license_data( $license_key, $responce_data );

					$responce_data['license_key'] = $license_key;

					wp_send_json(
						array(
							'success' => true,
							'message' => __( 'The license has been successfully activated', 'jet-form-builder' ),
							'data'    => $responce_data,
						)
					);

					break;

				case 'deactivate_license':
					$license_list = $this->get_license_data();

					$license_list = array_filter(
						$license_list,
						function ( $license_data ) use ( $license_key ) {
							return $license_data['license_key'] !== $license_key;
						}
					);

					update_option( $this->license_data_key, $license_list );

					wp_send_json(
						array(
							'success' => true,
							'message' => __( 'The license has been successfully deactivated', 'jet-form-builder' ),
							'data'    => $responce_data,
						)
					);

					break;
			}

			wp_send_json(
				array(
					'success' => false,
					'message' => __( 'Server error. Please, try again later', 'jet-form-builder' ),
					'data'    => array(),
				)
			);
		}

		/**
		 * Remote request to updater API.
		 *
		 * @since  1.0.0
		 * @return array|bool
		 */
		public function license_action_query( $action = '', $license = '' ) {

			$query_url = add_query_arg(
				array(
					'edd_action' => $action,
					'item_id'    => 9,
					'license'    => $license,
					'url'        => urlencode( $this->get_site_url() ),
				),
				$this->api_url
			);

			$response = wp_remote_get(
				$query_url,
				array(
					'timeout' => 60,
				)
			);

			if ( is_wp_error( $response ) ||
				( (int) wp_remote_retrieve_response_code( $response ) ) !== 200
			) {
				return false;
			}

			return json_decode( $response['body'], true );
		}

		/**
		 * [get description]
		 *
		 * @param  [type]  $setting [description]
		 * @param  boolean $default [description]
		 * @return [type]           [description]
		 */
		public function get_license_data() {

			if ( null === $this->license_data ) {
				$this->license_data = get_option( $this->license_data_key, array() );
			}

			return $this->license_data;
		}

		/**
		 * [set_license_data description]
		 *
		 * @param [type]  $setting [description]
		 * @param boolean $value   [description]
		 */
		public function add_license_data( $license_key = false, $license_data = array() ) {

			if ( ! $license_key ) {
				return;
			}

			$current_license_data = get_option( $this->license_data_key, array() );

			$license_data['license_key'] = $license_key;

			$current_license_data[] = $license_data;

			update_option( $this->license_data_key, $current_license_data );
		}

		/**
		 * [get_plugin_license_key description]
		 *
		 * @param  boolean $setting [description]
		 * @param  boolean $value   [description]
		 * @return [type]           [description]
		 */
		public function get_license_key() {

			$license_list = $this->get_license_data();

			if ( ! empty( $license_list ) ) {

				foreach ( $license_list as $key => $license_data ) {

					if ( 'expired' === $license_data['license'] ) {
						continue;
					}

					$is_expired = $this->license_expired_check( $license_data['expires'] );

					if ( $is_expired ) {
						$license_list[ $key ]['license'] = 'expired';

						continue;
					}

					if ( 'valid' === $license_data['license'] ) {
						return $license_data['license_key'];
					}
				}
			}

			return false;
		}

		/**
		 * [if_license_expire_check description]
		 *
		 * @param  boolean $expire_date [description]
		 * @return [type]               [description]
		 */
		public function license_expired_check( $expire_date = false, $day_to_expire = 0 ) {

			if ( '0000-00-00 00:00:00' === $expire_date
				|| '1000-01-01 00:00:00' === $expire_date
				|| 'lifetime' === $expire_date
			) {
				return false;
			}

			$current_time = time();

			$current_time = strtotime( sprintf( '+%s day', $day_to_expire ), $current_time );

			$expire_time = strtotime( $expire_date );

			if ( $current_time > $expire_time ) {
				return true;
			}

			return false;
		}

		/**
		 * @return array|string|string[]
		 */
		public function get_site_url() {
			$url_parts = parse_url( site_url( '/' ) );
			$site_url  = $url_parts['host'] . $url_parts['path'];
			$site_url  = preg_replace( '#^https?://#', '', rtrim( $site_url ) );

			return str_replace( 'www.', '', $site_url );
		}

		/**
		 * @param false $plugin_slug
		 *
		 * @return false|string
		 */
		public function package_url( $plugin_slug = false ) {

			$license_key = $this->get_license_key();

			if ( ! $license_key ) {
				return false;
			}

			$plugin_slug = $this->get_addon_slug_by_filename( $plugin_slug );

			return add_query_arg(
				array(
					'ct_api_action' => 'get_plugin',
					'license'       => $license_key,
					'slug'          => $plugin_slug,
					'url'           => urlencode( $this->get_site_url() ),
				),
				$this->api_url
			);
		}

		/**
		 * @param false $addon_filename
		 *
		 * @return string
		 */
		public function get_addon_slug_by_filename( $addon_filename = false ) {
			return explode( '/', $addon_filename )[0];
		}

		public function addon_install_action() {
			$data = $this->resolve_request();

			$plugin = $data['plugin'];

			$this->install_plugin( $plugin );

			wp_send_json(
				array(
					'success' => true,
					'message' => __( 'Success', 'jet-form-builder' ),
					'data'    => array(),
				)
			);
		}


		public function addon_update_action() {
			$data = $this->resolve_request();

			$plugin = $data['plugin'];

			$this->update_plugin( $plugin );

			wp_send_json(
				array(
					'success' => true,
					'message' => __( 'Success', 'jet-form-builder' ),
					'data'    => array(),
				)
			);
		}

		/**
		 * @param $plugin_file
		 * @param false $plugin_url
		 */
		public function install_plugin( $plugin_file, $plugin_url = false ) {

			$status = array();

			if ( ! current_user_can( 'install_plugins' ) ) {
				wp_send_json(
					array(
						'success' => false,
						'message' => __( 'Sorry, you are not allowed to install plugins on this site.', 'jet-form-builder' ),
						'data'    => array(),
					)
				);
			}

			if ( ! $plugin_url ) {
				$package = $this->package_url( $plugin_file );
			} else {
				$package = $plugin_url;
			}

			include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

			$skin     = new \WP_Ajax_Upgrader_Skin();
			$upgrader = new \Plugin_Upgrader( $skin );
			$result   = $upgrader->install( $package );

			if ( is_wp_error( $result ) ) {
				$status['errorCode']    = $result->get_error_code();
				$status['errorMessage'] = $result->get_error_message();

				wp_send_json(
					array(
						'success' => false,
						'message' => $result->get_error_message(),
						'data'    => array(),
					)
				);
			} elseif ( is_wp_error( $skin->result ) ) {
				$status['errorCode']    = $skin->result->get_error_code();
				$status['errorMessage'] = $skin->result->get_error_message();

				wp_send_json(
					array(
						'success' => false,
						'message' => $skin->result->get_error_message(),
						'data'    => array(),
					)
				);
			} elseif ( $skin->get_errors()->get_error_code() ) {
				$status['errorMessage'] = $skin->get_error_messages();

				wp_send_json(
					array(
						'success' => false,
						'message' => $skin->get_error_messages(),
						'data'    => array(),
					)
				);
			} elseif ( is_null( $result ) ) {
				global $wp_filesystem;

				$status['errorMessage'] = 'Unable to connect to the filesystem. Please confirm your credentials.';

				// Pass through the error from WP_Filesystem if one was raised.
				if ( $wp_filesystem instanceof \WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) {
					$status['errorMessage'] = esc_html( $wp_filesystem->errors->get_error_message() );
				}

				wp_send_json(
					array(
						'success' => false,
						'message' => $status['errorMessage'],
						'data'    => array(),
					)
				);
			}

			wp_send_json(
				array(
					'success' => true,
					'message' => __( 'The addon has been Installed', 'jet-form-builder' ),
					'data'    => \Jet_Form_Builder\Plugin::instance()->addons_manager->get_addon_data( $plugin_file ),
				)
			);
		}

		/**
		 * @param $plugin_file
		 */
		public function update_plugin( $plugin_file ) {

			$plugin = plugin_basename( sanitize_text_field( wp_unslash( $plugin_file ) ) );
			$slug   = dirname( $plugin );

			$status = array(
				'update'     => 'plugin',
				'slug'       => $slug,
				'oldVersion' => '',
				'newVersion' => '',
			);

			if ( ! current_user_can( 'update_plugins' ) || 0 !== validate_file( $plugin ) ) {
				wp_send_json(
					array(
						'success' => false,
						'message' => __( 'Sorry, you are not allowed to update plugins on this site.', 'jet-form-builder' ),
						'data'    => array(),
					)
				);
			}

			include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

			wp_update_plugins();

			$skin     = new \WP_Ajax_Upgrader_Skin();
			$upgrader = new \Plugin_Upgrader( $skin );
			$result   = $upgrader->bulk_upgrade( array( $plugin ) );

			$upgrade_messages = array();

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				$upgrade_messages = $skin->get_upgrade_messages();
			}

			if ( is_wp_error( $skin->result ) ) {
				wp_send_json(
					array(
						'success' => false,
						'message' => $skin->result->get_error_message(),
						'data'    => array(),
						'debug'   => $upgrade_messages,
					)
				);
			} elseif ( $skin->get_errors()->get_error_code() ) {
				wp_send_json(
					array(
						'success' => false,
						'message' => $skin->get_error_messages(),
						'data'    => array(),
						'debug'   => $upgrade_messages,
					)
				);

			} elseif ( is_array( $result ) && ! empty( $result[ $plugin ] ) ) {
				$plugin_update_data = current( $result );

				/*
				 * If the `update_plugins` site transient is empty (e.g. when you update
				 * two plugins in quick succession before the transient repopulates),
				 * this may be the return.
				 *
				 * Preferably something can be done to ensure `update_plugins` isn't empty.
				 * For now, surface some sort of error here.
				 */
				if ( true === $plugin_update_data ) {
					wp_send_json(
						array(
							'success' => false,
							'message' => __( 'Addon update failed.', 'jet-form-builder' ),
							'data'    => array(),
							'debug'   => $upgrade_messages,
						)
					);
				}

				wp_send_json(
					array(
						'success' => true,
						'message' => __( 'The addon has been updated', 'jet-form-builder' ),
						'data'    => \Jet_Form_Builder\Plugin::instance()->addons_manager->get_addon_data( $plugin_file ),
					)
				);

			} elseif ( false === $result ) {
				global $wp_filesystem;

				$errorMessage = 'Unable to connect to the filesystem. Please confirm your credentials.';

				// Pass through the error from WP_Filesystem if one was raised.
				if ( $wp_filesystem instanceof \WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) {
					$errorMessage = esc_html( $wp_filesystem->errors->get_error_message() );
				}

				wp_send_json(
					array(
						'success' => false,
						'message' => $errorMessage,
						'data'    => array(),
					)
				);
			}

			wp_send_json(
				array(
					'success' => false,
					'message' => __( 'Plugin update failed.', 'jet-form-builder' ),
					'data'    => array(),
				)
			);
		}

		/**
		 * @param $data
		 *
		 * @return mixed
		 */
		public function check_addons_update( $data ) {

			delete_site_transient( 'jfb_remote_addons_list' );

			$available_addons_data = \Jet_Form_Builder\Plugin::instance()->addons_manager->get_jfb_remote_plugin_list();

			foreach ( $available_addons_data as $key => $addon_info ) {

				$addon_slug             = $addon_info['slug'];
				$installed_user_plugins = \Jet_Form_Builder\Plugin::instance()->addons_manager->get_user_plugins();

				if ( ! isset( $installed_user_plugins[ $addon_slug ] ) ) {
					continue;
				}

				$current_version = $installed_user_plugins[ $addon_slug ]['currentVersion'];

				if ( version_compare( $current_version, $addon_info['version'], '<' ) ) {

					$plugin_file = $addon_info['slug'];
					$plugin_slug = \Jet_Form_Builder\Plugin::instance()->addons_manager->get_addon_slug_by_filename( $plugin_file );

					delete_site_transient( $plugin_slug . '_addon_info_data' );

					$update              = new \stdClass();
					$update->slug        = $plugin_slug;
					$update->plugin      = $plugin_file;
					$update->new_version = $addon_info['version'];
					$update->url         = false;
					$update->package     = $this->package_url( $plugin_file );

					$data->response[ $plugin_file ] = $update;
				}
			}

			return $data;
		}


		/**
		 * @param $data
		 *
		 * @return mixed
		 */
		public function modify_addons_page_localize_data( $data ) {
			$data['licenseMode'] = true;
			$data['licenseKey']  = $this->get_license_key();
			$data['licenseList'] = $this->get_license_data();

			return $data;
		}

		/**
		 * LicenseManager constructor.
		 */
		public function __construct() {

			add_action( 'wp_ajax_jfb_license_action', array( $this, 'license_action' ) );

			add_action( 'wp_ajax_jfb_addon_install_action', array( $this, 'addon_install_action' ) );

			add_action( 'wp_ajax_jfb_addon_update_action', array( $this, 'addon_update_action' ) );

			add_action( 'pre_set_site_transient_update_plugins', array( $this, 'check_addons_update' ) );

			add_filter( 'jfb-addons-page/license-mode', '__return_true' );

			add_filter( 'jfb-addons-page/page-localize-data', array( $this, 'modify_addons_page_localize_data' ) );
		}

		private function resolve_request(): array {
			if ( ! wp_verify_nonce(
				sanitize_key( $_POST['nonce'] ?? '' ),
				\Jet_Form_Builder\Addons\Manager::NONCE_ACTION
			) ) {
				wp_send_json(
					array(
						'success' => false,
						'message' => __( 'Server error. Please, try again later', 'jet-form-builder' ),
					)
				);
			}

			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$data = ( ! empty( $_POST['data'] ) ) ? \Jet_Form_Builder\Classes\Tools::maybe_recursive_sanitize( $_POST['data'] ) : false;

			if ( ! $data ) {
				wp_send_json(
					array(
						'success' => false,
						'message' => __( 'Server error. Please, try again later', 'jet-form-builder' ),
						'data'    => array(),
					)
				);
			}

			return $data;
		}

	}
}
