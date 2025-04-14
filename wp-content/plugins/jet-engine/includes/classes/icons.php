<?php
/**
 * Icons Manager
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Icons_Manager' ) ) {

	/**
	 * Define Jet_Engine_Icons_Manager class
	 */
	class Jet_Engine_Icons_Manager {

		/**
		 * Icons data.
		 *
		 * Holds the list of all the icons data.
		 *
		 * @var array
		 */
		private static $icons_data = [];

		private static $shared_styles = [];

		private static function init() {

			if ( ! empty( self::$icons_data ) ) {
				return;
			}

			$icons_data = [
				'font-awesome' => [
					'name'         => 'font-awesome',
					'label'        => esc_html__( 'Font Awesome 4', 'jet-engine' ),
					'icon_base'    => 'fa',
					'icon_prefix'  => 'fa-',
					'icon_css'     => jet_engine()->plugin_url( 'assets/lib/font-awesome/css/font-awesome.min.css' ),
					'icon_depends' => [],
					'icons'        => [ __CLASS__, 'get_fa_icons' ],
					'version'      => '4.7.0',
				],
				'dashicons' => [
					'name'         => 'dashicons',
					'label'        => esc_html__( 'Dashicons', 'jet-engine' ),
					'icon_base'    => 'dashicons',
					'icon_prefix'  => 'dashicons-',
					'icon_css'     => includes_url( 'css/dashicons.min.css' ),
					'icon_depends' => [],
					'icons'        => [ __CLASS__, 'get_dash_icons' ],
					'version'      => get_bloginfo( 'version' ),
				],
			];

			$icons_data = apply_filters( 'jet-engine/icons-manager/icons-data', $icons_data );

			self::$icons_data = $icons_data;
		}

		public static function get_icon_type_data( $type ) {
			self::init();
			return isset( self::$icons_data[ $type ] ) ? self::$icons_data[ $type ] : false;
		}

		public static function get_icons_libraries_list( $for_js = false ) {
			self::init();

			$list   = wp_list_pluck( self::$icons_data, 'label' );
			$result = [];

			if ( $for_js ) {
				foreach ( $list as $name => $label ) {
					$result[] = [
						'value' => $name,
						'label' => $label
					];
 				}
			} else {
				$result = $list;
			}

			return $result;
		}

		public static function get_iconpicker_data( $library ) {
			self::init();

			if ( empty( $library ) ) {
				$libraries = array_keys( self::$icons_data );
				$library   = isset( $libraries[0] ) ? $libraries[0] : '';
			}

			if ( empty( self::$icons_data[ $library ] ) ) {
				return [];
			}

			$icon_data = self::$icons_data[ $library ];

			return [
				'icon_set'     => $icon_data['name'],
				'icon_base'    => $icon_data['icon_base'],
				'icon_prefix'  => $icon_data['icon_prefix'],
				'icon_css'     => $icon_data['icon_css'],
				'icon_depends' => $icon_data['icon_depends'],
				'icons'        => $icon_data['icons'],
			];
		}

		public static function get_fa_icons() {
			ob_start();
			include jet_engine()->plugin_path( 'assets/lib/font-awesome/json/icons.json' );
			$json = ob_get_clean();

			$icons_list = [];
			$icons      = json_decode( $json, true );

			foreach ( $icons['icons'] as $icon ) {
				$icons_list[] = $icon['id'];
			}

			return $icons_list;
		}

		public static function get_dash_icons() {
			ob_start();
			include jet_engine()->plugin_path( 'assets/lib/dashicons/icons.json' );
			$json = ob_get_clean();

			$icons = json_decode( $json, true );
			$icons = array_keys( $icons );

			return $icons;
		}

		public static function get_icon_html( $icon = '', $attrs = [], $tag = 'i' ) {

			if ( empty( $icon ) ) {
				return '';
			}

			self::init();

			$icon_type = self::find_icon_type( $icon );

			if ( ! empty( $icon_type ) ) {
				self::enqueue_icon_assets( $icon_type );
			}

			if ( empty( $attrs['class'] ) ) {
				$attrs['class'] = $icon;
			} else {
				if ( is_array( $attrs['class'] ) ) {
					$attrs['class'][] = $icon;
				} else {
					$attrs['class'] .= ' ' . $icon;
				}
			}

			$custom_render = apply_filters( 'jet-engine/icons-manager/custom-icon-html', false, $icon, $attrs, $tag );

			if ( $custom_render ) {
				return $custom_render;
			}

			return '<' . $tag . ' ' . Jet_Engine_Tools::get_attr_string( $attrs ) . '></' . $tag . '>';
		}

		public static function find_icon_type( $icon ) {
			self::init();

			$icon_type = false;

			foreach ( self::$icons_data as $type => $data ) {

				$icon_format = $data['icon_prefix'];

				if ( ! empty( $data['icon_base'] ) ) {
					$icon_format = $data['icon_base'] . ' ' . $icon_format;
				}

				if ( 0 === strpos( $icon, $icon_format ) ) {
					$icon_type = $type;
					break;
				}
			}

			return $icon_type;
		}

		public static function enqueue_icon_assets( $icon_type ) {
			self::init();

			if ( empty( self::$icons_data[ $icon_type ] ) ) {
				return;
			}

			$icon_data = self::$icons_data[ $icon_type ];

			if ( empty( $icon_data['icon_css'] ) ) {
				return;
			}

			if ( wp_style_is( $icon_data['name'] ) ) {
				return;
			}

			$depends = [];

			if ( ! empty( $icon_data['icon_depends'] ) ) {

				foreach ( (array) $icon_data['icon_depends'] as $css_url ) {

					if ( ! isset( self::$shared_styles[ $css_url ] ) ) {
						$style_handle = 'jet-engine-icons-shared-' . count( self::$shared_styles );

						wp_register_style(
							$style_handle,
							$css_url,
							false,
							$icon_data['version']
						);

						self::$shared_styles[ $css_url ] = $style_handle;
					}

					$depends[] = self::$shared_styles[ $css_url ];
				}

			}

			wp_enqueue_style(
				$icon_data['name'],
				$icon_data['icon_css'],
				$depends,
				$icon_data['version']
			);
		}

	}
}