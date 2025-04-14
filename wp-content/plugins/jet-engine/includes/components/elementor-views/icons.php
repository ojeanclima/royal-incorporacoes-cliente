<?php
/**
 * Icons integration class.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use Elementor\Plugin;
use Elementor\Icons_Manager;

class Jet_Engine_Elementor_Icons {

	public function __construct() {
		add_filter( 'jet-engine/icons-manager/icons-data', [ $this, 'register_elementor_icons' ] );
	}

	public function register_elementor_icons( $data ) {

		$icons_types = Icons_Manager::get_icon_manager_tabs();

		$e_font_icon_svg = Plugin::$instance->experiments->is_feature_active( 'e_font_icon_svg' );

		if ( $e_font_icon_svg ) {
			$icons_types = array_replace( $this->get_initial_tabs(), $icons_types );
		}

		foreach ( $icons_types as $type => $icon_data ) {
			$data[ $type ] = [
				'name'         => 'elementor-icons-' . $icon_data['name'],
				'label'        => $icon_data['label'],
				'icon_base'    => $icon_data['displayPrefix'],
				'icon_prefix'  => $icon_data['prefix'],
				'icon_css'     => $icon_data['url'],
				'icon_depends' => ! empty( $icon_data['enqueue'] ) ? $icon_data['enqueue'] : [],
				'version'      => $icon_data['ver'],
				'icons'        => function() use ( $icon_data ) {
					return $this->get_icons( $icon_data );
				},
			];
		}

		return $data;
	}

	public function get_initial_tabs() {

		$fa_asset_url = ELEMENTOR_ASSETS_URL . 'lib/font-awesome/%s';

		$initial_tabs = [
			'fa-regular' => [
				'name'          => 'fa-regular',
				'label'         => esc_html__( 'Font Awesome - Regular', 'jet-engine' ),
				'url'           => sprintf( $fa_asset_url, 'css/regular.min.css' ),
				'enqueue'       => [ sprintf( $fa_asset_url, 'css/fontawesome.min.css' ) ],
				'prefix'        => 'fa-',
				'displayPrefix' => 'far',
				'labelIcon'     => 'fab fa-font-awesome-alt',
				'ver'           => '5.15.3',
				'fetchJson'     => sprintf( $fa_asset_url, 'js/regular.js' ),
				'native'        => true,
			],
			'fa-solid' => [
				'name'          => 'fa-solid',
				'label'         => esc_html__( 'Font Awesome - Solid', 'jet-engine' ),
				'url'           => sprintf( $fa_asset_url, 'css/solid.min.css' ),
				'enqueue'       => [ sprintf( $fa_asset_url, 'css/fontawesome.min.css' ) ],
				'prefix'        => 'fa-',
				'displayPrefix' => 'fas',
				'labelIcon'     => 'fab fa-font-awesome',
				'ver'           => '5.15.3',
				'fetchJson'     => sprintf( $fa_asset_url, 'js/solid.js' ),
				'native'        => true,
			],
			'fa-brands' => [
				'name'          => 'fa-brands',
				'label'         => esc_html__( 'Font Awesome - Brands', 'jet-engine' ),
				'url'           => sprintf( $fa_asset_url, 'css/brands.min.css' ),
				'enqueue'       => [ sprintf( $fa_asset_url, 'css/fontawesome.min.css' ) ],
				'prefix'        => 'fa-',
				'displayPrefix' => 'fab',
				'labelIcon'     => 'fab fa-font-awesome-flag',
				'ver'           => '5.15.3',
				'fetchJson'     => sprintf( $fa_asset_url, 'js/brands.js' ),
				'native'        => true,
			],
		];

		/**
		 * Initial icon manager tabs.
		 *
		 * Filters the list of initial icon manager tabs.
		 *
		 * @param array $icon_manager_tabs Initial icon manager tabs.
		 */
		$initial_tabs = apply_filters( 'elementor/icons_manager/native', $initial_tabs );

		return $initial_tabs;
	}

	public function get_icons( $icon_data ) {

		if ( ! empty( $icon_data['icons'] ) ) {
			return $icon_data['icons'];
		}

		$icons = [];

		if ( ! empty( $icon_data['fetchJson'] ) ) {

			$json_path = false;

			if ( false !== strpos( $icon_data['fetchJson'], plugins_url() ) ) {
				$json_path = str_replace( plugins_url(), wp_normalize_path( WP_PLUGIN_DIR ), $icon_data['fetchJson'] );
			} else if ( false !== strpos( $icon_data['fetchJson'], content_url() ) ) {
				$json_path = str_replace( content_url(), wp_normalize_path( WP_CONTENT_DIR ), $icon_data['fetchJson'] );
			}

			$json_path = apply_filters( 'jet-engine/elementor-view/icons/json-patch', $json_path, $icon_data['fetchJson'], $icon_data );

			if ( $json_path && file_exists( $json_path ) ) {
				ob_start();
				include $json_path;
				$json = ob_get_clean();

				$icons = json_decode( $json, true );
				$icons = isset( $icons['icons'] ) ? $icons['icons'] : $icons;
			}
		}

		return $icons;
	}
}