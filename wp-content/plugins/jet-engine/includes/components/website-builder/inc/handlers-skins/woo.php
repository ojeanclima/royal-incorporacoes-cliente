<?php
namespace Jet_Engine\Website_Builder\Handlers_Skins;

use \Jet_Engine\Website_Builder\Handler;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Woo extends Base {

	/**
	 * Get skin ID
	 *
	 * @return string
	 */
	public function get_id() {
		return Handler::WOO_ID;
	}

	/**
	 * Get skin title
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'WooCommerce', 'jet-engine' );
	}

	/**
	 * Render single results row
	 *
	 * @param  array  $row Results data row.
	 * @return string
	 */
	public function skin_data_row( $row = [] ) {

		$result = '';

		$continue_setup = add_query_arg(
			[
				'page' => 'wc-admin',
				'path' => '/setup-wizard',
			],
			admin_url( 'admin.php' )
		);

		$products_edit = add_query_arg(
			[
				'post_type' => 'product',
			],
			admin_url( 'edit.php' )
		);

		$dashboard = add_query_arg(
			[
				'page' => 'wc-admin'
			],
			admin_url( 'admin.php' )
		);

		$result .= $this->data_row_title(
			$row['name'],
			sprintf( '<a href="%1$s">%2$s</a>', $dashboard, __( 'Dashboard', 'jet-engine' ) )
		);

		$items = [];

		$install_ts = get_option( 'woocommerce_admin_install_timestamp' );

		if ( ! $install_ts ) {
			$items[] = $this->data_row_item( sprintf(
				'<a href="%1$s">%2$s</a>',
				$continue_setup, __( 'Continue WooCommerce Setup', 'jet-engine' )
			) );
		}

		$items[] = $this->data_row_item( sprintf(
			'<a href="%1$s">%2$s</a>',
			$products_edit, __( 'Go to Products', 'jet-engine' )
		) );

		$result .= $this->data_row_content( implode( '|', $items ) );

		return $result;
	}
}
