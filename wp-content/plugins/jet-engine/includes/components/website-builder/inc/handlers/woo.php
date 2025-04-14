<?php
namespace Jet_Engine\Website_Builder\Handlers;

use \Jet_Engine\Website_Builder\Handler;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Woo extends Base {

	/**
	 * Get handler ID
	 *
	 * @return string
	 */
	public function get_id() {
		return Handler::WOO_ID;
	}

	/**
	 * Returns list of Woo built-in taxonomies
	 *
	 * @return [type] [description]
	 */
	public function built_in_taxonomies() {
		return [
			'product_cat' => 'product_cat',
			'product_tag' => 'product_tag',
			'product_category' => 'product_cat',
		];
	}

	/**
	 * Handle entities registration/creation
	 *
	 * @param  array $data Data to register.
	 * @return bool
	 */
	public function handle( array $data = [] ) {

		$result = true;

		if ( empty( $data ) ) {
			return;
		}

		if ( ! function_exists( 'WC' ) ) {

			$installed = false;

			try {
				$installed = $this->install_plugin();
			} catch ( \Exception $e ) {
				// Add errors processing
			}

			if ( ! $installed ) {
				return $result;
			}
		}

		if ( defined( 'JET_ENGINE_WEBSITE_BUILDER_DEBUG' ) && JET_ENGINE_WEBSITE_BUILDER_DEBUG ) {

			$this->log_entity( [
				'id'   => 1,
				'name' => 'WooCommerce',
			] );

			return;
		}

		$tax_handler           = Handler::instance()->get_handler( Handler::TAXONOMIES_ID );
		$additional_taxonomies = [];
		$built_in_taxonomies   = $this->built_in_taxonomies();

		if ( ! empty( $data['taxonomies'] ) ) {
			foreach ( $data['taxonomies'] as $tax ) {
				if ( isset( $built_in_taxonomies, $tax['name'] ) && ! empty( $tax['sampleData'] ) ) {
					$tax_name = $built_in_taxonomies[ $tax['name'] ];
					$tax_handler->create_sample_terms( $tax_name, $tax['sampleData'] );
				} elseif ( ! isset( $built_in_taxonomies, $tax['name'] ) ) {
					$tax['post_type'] = 'product';
					$additional_taxonomies[] = $tax;
				}
			}
		}

		if ( ! empty( $additional_taxonomies ) ) {
			$tax_handler->handle( $additional_taxonomies );
		}

		if ( ! empty( $data['metaFields'] ) ) {
			foreach ( $data['metaFields'] as $field ) {
				if (
					! isset( $this->reserved_fields()[ $field['name'] ] )
					&& ! taxonomy_exists( $this->get_attr_tax_name( $field['name'] ) )
				) {
					$this->create_attribute( [
						'name'         => ucfirst( $field['name'] ),
						'slug'         => $field['name'],
						'type'         => 'select',
						'order_by'     => 'menu_order',
						'has_archives' => false,
					] );

					if ( ! empty( $tax['sampleData'] ) ) {
						$tax_handler->create_sample_terms(
							$this->get_attr_tax_name( $field['name'] ),
							$tax['sampleData']
						);
					}
				}
			}
		}

		$this->log_entity( [
			'id'   => 1,
			'name' => 'WooCommerce',
		] );

		return $result;
	}

	/**
	 * Create WC attribute.
	 * We can't use native WC method because it's not available on first install.
	 *
	 * @param  array  $args [description]
	 * @return [type]       [description]
	 */
	public function create_attribute( $args = [] ) {

		global $wpdb;

		$format = [ '%s', '%s', '%s', '%s', '%d' ];
		$data   = [
			'attribute_label'   => $args['name'],
			'attribute_name'    => $slug['slug'],
			'attribute_type'    => $args['type'],
			'attribute_orderby' => $args['order_by'],
			'attribute_public'  => isset( $args['has_archives'] ) ? (int) $args['has_archives'] : 0,
		];

		$wpdb->insert(
			$wpdb->prefix . 'woocommerce_attribute_taxonomies',
			$data,
			$format
		);
	}

	/**
	 * Get WC-prefixed taxonomy name for the attribute.
	 * We can't use native WC method because it's not available on first install.
	 *
	 * @param  string $attr_name Raw attr name.
	 * @return [type]            [description]
	 */
	public function get_attr_tax_name( $attr_name = '' ) {
		return 'pa_' . $attr_name;
	}

	/**
	 * Get Woo reserved fields map
	 *
	 * @return [type] [description]
	 */
	public function reserved_fields() {
		return [
			'price'         => '_price',
			'_price'        => '_price',
			'stock_status'  => '_stock_status',
			'_stock_status' => '_stock_status',
			'stock'         => '_stock',
			'_stock'        => '_stock',
		];
	}

	/**
	 * Install JSF plugin
	 */
	public function install_plugin() {

		if ( ! function_exists( 'plugins_api' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		}

		$api = plugins_api(
			'plugin_information',
			array( 'slug' => 'woocommerce', 'fields' => array( 'sections' => false ) )
		);

		if ( is_wp_error( $api ) ) {
			throw new \Exception( 'Plugins API error' );
		}

		if ( isset( $api->download_link ) ) {
			$package = $api->download_link;
		}

		if ( ! $package ) {
			throw new \Exception( 'Please activate license which includes JetSmartFilters pliugin' );
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

		$result = activate_plugin( 'woocommerce/woocommerce.php', '', false, true );

		if ( is_wp_error( $result ) ) {
			throw new \Exception( $result->get_error_messages() );
		}

		return true;
	}
}
