<?php
namespace Jet_Engine\Modules\Profile_Builder;

class Settings {

	private $settings   = null;
	private $pages      = null;

	public $account_key = 'account_page_structure';
	public $user_key    = 'user_page_structure';

	public $default_settings = array(
		'user_page_rewrite'      => 'login',
		'not_logged_in_action'   => 'login_redirect',
		'template_mode'          => 'rewrite',
		'posts_restrictions'     => array(),
		'user_page_seo_title'    => '%username% %sep% %sitename%',
		'user_page_seo_desc'     => '',
		'user_page_seo_image'    => '',
		'account_page_seo_title' => '%pagetitle% %sep% %sitename%',
		'account_page_seo_desc'  => '',
	);

	private $nonce_action = 'jet-engine-profile-builder';

	/**
	 * Constructor for the class
	 */
	public function __construct() {

		add_action( 'admin_menu', [ $this, 'register_menu_page' ], 40 );

		if ( $this->is_profile_builder_page() ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'menu_page_assets' ] );
		}

		add_action( 'wp_ajax_jet_engine_save_settings', [ $this, 'save_settings' ] );
		add_action( 'wp_ajax_jet_engine_create_profile_template', [ $this, 'create_template' ] );

		add_filter( 'jet-engine/rest-api/search-posts/result-item', [ $this, 'adjust_serach_results' ], 10, 3 );

		add_filter( 'display_post_states', [ $this, 'add_profile_builder_post_state' ], 100, 2 );

	}

	public function get_post_states() {
		return array(
			'account_page'     => __( 'Account Page', 'jet-engine' ),
			'single_user_page' => __( 'Single User Page', 'jet-engine' ),
			'users_page'       => __( 'Users Page', 'jet-engine' ),
		);
	}

	public function add_profile_builder_post_state( $states, $post ) {
		foreach ( $this->get_pages() as $page => $id ) {
			$id = ( int ) apply_filters( 'jet-engine/profile-builder/settings/post-state-id', $id, $post );

			if ( $id === $post->ID ) {
				$states[ 'jet-engine-pb-' . $page ] = __( 'Jet Engine ', 'jet-engine' ) . $this->get_post_states()[ $page ];
			}
		}

		return $states;
	}

	public function adjust_serach_results( $result, $post, $context ) {
		if ( 'profile-builder' === $context ) {
			$post_types = $this->get_search_post_types();
			$result['label'] = sprintf(
				'%1$s (%2$s)',
				$result['label'], $post_types[ $post->post_type ] ?? $post->post_type
			);
		}
		return $result;
	}

	public function get_search_post_types() {
		return apply_filters( 'jet-engine/profile-builder/settings/template-sources', array(
			jet_engine()->listings->post_type->slug() => __( 'Listing Item', 'jet-engine' ),
		) );
	}

	/**
	 * AJAX callback to create new profile template
	 */
	public function create_template() {

		if ( empty( $_REQUEST['_nonce'] ) || ! wp_verify_nonce( $_REQUEST['_nonce'], $this->nonce_action ) ) {
			wp_send_json_error( array(
				'message' => __( 'The page is expired. Please reload page and try again.', 'jet-engine' ),
			) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Access denied', 'jet-engine' ) ) );
		}

		$template_name = ( ! empty( $_REQUEST['template_name'] ) ) ? $_REQUEST['template_name'] : false;
		$template_type = ( ! empty( $_REQUEST['template_type'] ) ) ? $_REQUEST['template_type'] : jet_engine()->listings->post_type->slug();
		$template_view = ( ! empty( $_REQUEST['template_view'] ) ) ? $_REQUEST['template_view'] : false;

		if ( ! $template_name ) {
			wp_send_json_error( array( 'message' => __( 'Template name is empty', 'jet-engine' ) ) );
		}

		$template_data = apply_filters(
			'jet-engine/profile-builder/create-template/' . $template_type,
			false,
			$template_name,
			$template_view
		);

		if ( empty( $template_data ) ) {
			wp_send_json_error( array( 'message' => __( 'Template not created. Please try again with different template type.', 'jet-engine' ) ) );
		}

		wp_send_json_success( $template_data );

	}

	/**
	 * Save user settings
	 *
	 * @return [type] [description]
	 */
	public function save_settings() {

		if ( empty( $_REQUEST['_nonce'] ) || ! wp_verify_nonce( $_REQUEST['_nonce'], $this->nonce_action ) ) {
			wp_send_json_error( array(
				'message' => __( 'The page is expired. Please reload page and try again.', 'jet-engine' ),
			) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Access denied', 'jet-engine' ) ) );
		}

		$settings = isset( $_REQUEST['settings'] ) ? $_REQUEST['settings'] : false;

		if ( ! $settings || ! is_array( $settings ) ) {
			wp_send_json_error( array( 'message' => __( 'Settings not found in request', 'jet-engine' ) ) );
		}

		$settings = wp_parse_args( $settings, $this->default_settings );
		$settings = wp_unslash( $settings );

		foreach ( $settings as $key => $value ) {
			if ( in_array( $value, array( 'true', 'false' ) ) ) {
				$settings[ $key ] = filter_var( $value, FILTER_VALIDATE_BOOLEAN );
			} elseif ( in_array( $key, array( $this->account_key, $this->user_key ) ) ) {
				if ( ! empty( $settings[ $key ] ) ) {

					$sanitized_settings = array();

					foreach ( $settings[ $key ] as $item ) {
						$item['collapsed'] = true;

						foreach ( $item as $item_key => $item_value ) {
							if ( in_array( $item_value, array( 'true', 'false' ) ) ) {
								$item[ $item_key ] = filter_var( $item_value, FILTER_VALIDATE_BOOLEAN );
							}
						}

						$sanitized_settings[] = $item;
					}

					$settings[ $key ] = $sanitized_settings;

				}
			}
		}

		update_option( Module::instance()->slug, $settings );

		flush_rewrite_rules( true );
		wp_send_json_success();

	}

	/**
	 * Enqueue menu page assets
	 *
	 * @return [type] [description]
	 */
	public function menu_page_assets() {

		$module_data = jet_engine()->framework->get_included_module_data( 'cherry-x-vue-ui.php' );
		$ui          = new \CX_Vue_UI( $module_data );

		$ui->enqueue_assets();

		wp_enqueue_script(
			'jet-engine-profile-builder-settings',
			Module::instance()->module_url( 'assets/js/admin/settings.js' ),
			array( 'cx-vue-ui', 'wp-api-fetch' ),
			jet_engine()->get_version(),
			true
		);

		$post_types = $this->get_search_post_types();
		$settings   = $this->get();

		if ( ! empty( $settings['posts_restrictions'] ) ) {
			for ( $i = 0; $i < count( $settings['posts_restrictions'] ); $i++ ) {
				if ( empty( $settings['posts_restrictions'][ $i ]['id'] ) ) {
					$settings['posts_restrictions'][ $i ]['id'] = rand( 1000, 9999 );
				}
			}
		}

		if ( ! empty( $settings['account_page_structure'] ) ) {
			for ( $i = 0; $i < count( $settings['account_page_structure'] ); $i++ ) {
				if ( empty( $settings['account_page_structure'][ $i ]['id'] ) ) {
					$settings['account_page_structure'][ $i ]['id'] = rand( 1000, 9999 );
				}
			}
		}

		if ( ! empty( $settings['user_page_structure'] ) ) {
			for ( $i = 0; $i < count( $settings['user_page_structure'] ); $i++ ) {
				if ( empty( $settings['user_page_structure'][ $i ]['id'] ) ) {
					$settings['user_page_structure'][ $i ]['id'] = rand( 1000, 9999 );
				}
			}
		}

		if ( ! isset( $settings['user_page_seo_title'] ) ) {
			$settings['user_page_seo_title'] = $this->default_settings['user_page_seo_title'];
		}

		$roles = \Jet_Engine_Tools::get_user_roles_for_js();

		$roles[] = array(
			'value' => 'jet-engine-guest',
			'label' => __( 'Guests (Not logged-in users)', 'jet-engine' ),
		);

		$all_title_macros  = Module::instance()->frontend->get_profile_builder_macros();
		$title_macros_list = array();

		foreach ( $all_title_macros as $macro => $args ) {
			$title_macros_list[] = array(
				'label'        => $args['label'],
				'macro'        => '%' . ( ! empty( $args['variable'] ) ? $args['variable'] : $macro ) . '%',
				'allowed_tabs' => $args['allowed_tabs'] ?? array( 'user_page' ),
			);
		}

		wp_localize_script(
			'jet-engine-profile-builder-settings',
			'JetEngineProfileBuilder',
			array(
				'search_api'         => jet_engine()->api->get_route( 'search-posts' ),
				'search_in'          => apply_filters(
					'jet-engine/profile-builder/settings/template-post-types',
					array_keys( $post_types )
				),
				'listing_views'      => jet_engine()->listings->post_type->get_listing_views(),
				'template_sources'   => $post_types,
				'settings'           => $settings,
				'pages'              => $this->get_pages_for_options(),
				'visibility_options' => array(
					array(
						'value' => 'all',
						'label' => __( 'All', 'jet-engine' ),
					),
					array(
						'value' => 'owner',
						'label' => __( 'Owner', 'jet-engine' ),
					),
				),
				'user_roles' => $roles,
				'post_types' => \Jet_Engine_Tools::get_post_types_for_js(),
				'not_logged_in_actions' => array(
					array(
						'value' => 'login_redirect',
						'label' => __( 'Redirect to default WordPress login page', 'jet-engine' ),
					),
					array(
						'value' => 'page_redirect',
						'label' => __( 'Redirect to page', 'jet-engine' ),
					),
					array(
						'value' => 'template',
						'label' => __( 'Show template', 'jet-engine' ),
					),
				),
				'rewrite_options' => array(
					array(
						'value' => 'login',
						'label' => __( 'Username', 'jet-engine' ),
					),
					array(
						'value' => 'user_nicename',
						'label' => __( 'User nicename', 'jet-engine' ),
					),
					array(
						'value' => 'id',
						'label' => __( 'User ID', 'jet-engine' ),
					),
				),
				'profile_builder_macros' => $title_macros_list,
				'user_page_image_fields' => $this->get_user_image_fields(),
				'_nonce' => wp_create_nonce( $this->nonce_action ),
			)
		);

		wp_enqueue_style(
			'jet-engine-dashboard',
			jet_engine()->plugin_url( 'assets/css/admin/dashboard.css' ),
			array(),
			jet_engine()->get_version()
		);

		add_action( 'admin_footer', array( $this, 'print_templates' ) );
		add_action( 'admin_footer', array( $this, 'print_inline_css' ) );

	}

	/**
	 * Get Jet Engine user fields
	 *
	 * @return array Array of user image fields
	 */
	public function get_user_image_fields() {

		$fields = array(
			array(
				'value' => '',
				'label' => esc_html__( 'Select...', 'jet-engine' ),
			),
		);

		$meta_fields = array();

		if ( jet_engine()->meta_boxes ) {
			$meta_fields = jet_engine()->meta_boxes->get_fields_for_select( 'media', 'blocks', 'user' );

			$meta_fields = array_map( function( $option ) {

				if ( ! empty( $option['values'] ) ) {
					$option['options'] = $option['values'];
					unset( $option['values'] );
				}

				return $option;

			}, $meta_fields );
		}

		return array_merge( $fields, $meta_fields );
	}

	/**
	 * Print profile builder settings templates
	 *
	 * @return void
	 */
	public function print_templates() {

		ob_start();
		include jet_engine()->modules->modules_path( 'profile-builder/inc/templates/admin/settings.php' );
		$content = ob_get_clean();

		printf( '<script type="text/x-template" id="jet-profile-builder">%s</script>', $content );

		ob_start();
		include jet_engine()->modules->modules_path( 'profile-builder/inc/templates/admin/macros.php' );
		$content = ob_get_clean();

		printf( '<script type="text/x-template" id="jet-profile-builder-macros">%s</script>', $content );

		ob_start();
		include jet_engine()->modules->modules_path( 'profile-builder/inc/templates/admin/new-template.php' );
		$content = ob_get_clean();

		printf( '<script type="text/x-template" id="jet-profile-builder-new-template">%s</script>', $content );

	}

	public function print_inline_css() {
		$css = '
			.cx-vui-component--has-macros .cx-vui-component__control {
				display: flex;
				position: relative;
				align-items: flex-start;
			}
			
			.jet-profile-macros {
				width: 32px;
				height: 32px;
			}

			.jet-profile-macros__trigger {
				height: 32px;
				cursor: pointer;
				display: flex;
				align-items: center;
				align-content: center;
				justify-content: center
			}
			
			.jet-profile-macros__trigger svg path {
				fill: #7b7e81
			}
			
			.jet-profile-macros__trigger:hover svg path {
				fill: #007cba
			}
			
			.jet-profile-macros__trigger-icon {
				width: 24px;
				height: auto
			}
			
			.jet-profile-macros__popup {
				position: absolute;
				left: 0;
				right: 0;
				top: calc(100% + 5px);
				background: #fff;
				border: 1px solid #ececec;
				box-shadow: 0 2px 6px rgba(35,40,45,.07);
				border-radius: 6px;
				z-index: 9999;
				overflow: auto;
				max-height: 200px;
			}
			
			.jet-profile-macros__item {
				display: flex;
				justify-content: space-between;
				align-items: flex-start;
				gat: 5px;
				padding: 10px;
				cursor: pointer;
			}
			
			.jet-profile-macros__item:hover {
				background: #f8f9fa;
			}
			
			.jet-profile-macros__item strong {
				font-weight: 500;
			}
			
			.jet-profile-macros__item code {
				font-size: 12px;
				border-radius: 3px;
				white-space: nowrap;
			}
			
			.jet-profile-macros__item + .jet-profile-macros__item {
				border-top: 1px solid #ececec;
			}

			.jet-profile-template {
				padding: 4px 0 0 0;
			}

			.jet-profile-template .jet-profile-template__trigger {
				display: inline-flex;
				line-height: 14px;
			}

			.jet-profile-template .jet-profile-template__trigger:focus {
				text-decoration: none;
			}
		';

		printf( '<style>%s</style>', $css );
	}

	/**
	 * Get saved settings by name or get all settings
	 *
	 * @param  string $setting Setting name. If omitted - all settings returned
	 * @return mixed           Setting value. Array of settings if no setting name provided
	 */
	public function get( $setting = null, $default = false ) {

		if ( null === $this->settings ) {
			$this->settings = get_option( Module::instance()->slug, $this->default_settings );
		}

		if ( ! isset( $this->settings['posts_restrictions'] ) ) {
			$this->settings['posts_restrictions'] = array();
		}

		if ( ! $setting ) {
			return $this->settings;
		}

		if ( ! $default ) {
			$default = ! empty( $this->default_settings[ $setting ] ) ? $this->default_settings[ $setting ] : $default;
		}

		$this->settings = apply_filters( 'jet-engine/profile-builder/settings', $this->settings );

		return isset( $this->settings[ $setting ] ) ? $this->settings[ $setting ] : $default;

	}

	/**
	 * Returns pages settings
	 *
	 * @return [type] [description]
	 */
	public function get_pages() {

		if ( null !== $this->pages ) {
			return $this->pages;
		}

		$settings = $this->get();
		$pages    = array(
			'account_page'     => false,
			'single_user_page' => 'enable_single_user_page',
			'users_page'       => 'enable_users_page',
		);

		$this->pages = array();

		foreach ( $pages as $page => $enabled_key ) {
			if ( ! $enabled_key && ! empty( $settings[ $page ] ) ) {
				$this->pages[ $page ] = $settings[ $page ];
			} elseif ( $enabled_key && ! empty( $settings[ $enabled_key ] ) && ! empty( $settings[ $page ] ) ) {
				$this->pages[ $page ] = $settings[ $page ];
			} else {
				$this->pages[ $page ] = false;
			}
		}

		return $this->pages;

	}

	/**
	 * Returns URL to profile page by page settings key
	 *
	 * @param  string $page [description]
	 * @return [type]       [description]
	 */
	public function get_page_url( $page = 'account_page' ) {

		$pages = $this->get_pages();

		if ( empty( $pages[ $page ] ) ) {
			return false;
		}

		$page_id = $pages[ $page ];
		$url     = get_permalink( $page_id );

		preg_match( '/\?.+$/', $url, $params );

		$url = preg_replace( '/\?.+$/', '', $url );

		if ( 'single_user_page' === $page ) {
			$url .= Module::instance()->query->get_queried_user_slug() . '/';
		}

		$url = trailingslashit( $url );

		if ( ! empty( $params[0] ) ) {
			$url .= $params[0];
		}

		return $url;

	}

	/**
	 * Return URL to subpage by passed page name and subpage slug
	 *
	 * @return [type] [description]
	 */
	public function get_subpage_url( $slug = null, $page = 'account_page' ) {

		$page_url = $this->get_page_url( $page );

		if ( ! $page_url ) {
			return false;
		} else {

			preg_match( '/\?.+$/', $page_url, $params );

			$page_url = preg_replace( '/\?.+$/', '', $page_url );

			$page_data = $this->get_subpage_data( $slug, $page );

			$url = ! empty( $slug ) ? $page_url . $slug . '/' : $page_url;
			$url .= $params[0] ?? '';

			return apply_filters( 'jet-engine/profile-builder/subpage-url', $url, $slug, $page, $page_data, $this );

		}

	}

	/**
	 * Return the subpage data by passed page name and subpage slug.
	 *
	 * @param null   $slug
	 * @param string $page
	 *
	 * @return mixed
	 */
	public function get_subpage_data( $slug = null, $page = 'account_page' ) {

		$page_data = null;
		$pages     = ( 'single_user_page' === $page ) ? $this->get( $this->user_key, array() ) : $this->get( $this->account_key, array() );

		if ( ! empty( $pages ) ) {

			$pages = array_values( $pages );

			foreach ( $pages as $index => $_page ) {

				if ( ! empty( $_page['template'] ) && ! is_array( $_page['template'] ) ) {
					$_page['template'] = [ $_page['template'] ];
				}

				if ( ! empty( $_page['slug'] ) && $_page['slug'] === $slug ) {
					$page_data = $_page;
					break;
				}
			}
		}

		return $page_data;
	}

	/**
	 * Check if profile builder page is currently displaying
	 *
	 * @return boolean [description]
	 */
	public function is_profile_builder_page() {
		return ( is_admin() && isset( $_GET['page'] ) && Module::instance()->slug === $_GET['page'] );
	}

	/**
	 * Register menu page
	 *
	 * @return void
	 */
	public function register_menu_page() {

		add_submenu_page(
			jet_engine()->admin_page,
			__( 'Profile Builder' ),
			__( 'Profile Builder' ),
			'manage_options',
			Module::instance()->slug,
			array( $this, 'render_menu_page' )
		);

	}

	/**
	 * Retrns pages list to use in select options
	 *
	 * @return [type] [description]
	 */
	public function get_pages_for_options() {
		$pages = get_pages();
		return \Jet_Engine_Tools::prepare_list_for_js( $pages, 'ID', 'post_title' );
	}

	/**
	 * Render menu page
	 *
	 * @return [type] [description]
	 */
	public function render_menu_page() {
		echo '<div id="jet_engine_profile_builder"></div>';
	}

}
