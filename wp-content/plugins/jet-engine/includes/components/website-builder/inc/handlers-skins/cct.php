<?php
namespace Jet_Engine\Website_Builder\Handlers_Skins;

use \Jet_Engine\Website_Builder\Handler;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class CCT extends Base {

	/**
	 * Get skin ID
	 *
	 * @return string
	 */
	public function get_id() {
		return Handler::CCT_ID;
	}

	/**
	 * Get skin title
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Custom Content Types', 'jet-engine' );
	}

	/**
	 * Render single results row
	 *
	 * @param  array  $row Results data row.
	 * @return string
	 */
	public function skin_data_row( $row = [] ) {

		$result = '';

		$cct_edit = add_query_arg(
			[
				'page' => 'jet-engine-cct',
				'cct_action' => 'edit',
				'id' => $row['id'],
			],
			admin_url( 'admin.php' )
		);

		$cct_items = self::get_items_url( $row['slug'] );

		$result .= $this->data_row_title(
			$row['name'],
			sprintf( '<a href="%1$s">%2$s</a>', $cct_edit, __( 'Edit', 'jet-engine' ) )
		);

		$items = [
			$this->data_row_item( sprintf(
				'<a href="%1$s">%2$s</a>',
				$cct_items, __( 'Add CCT items', 'jet-engine' )
			) ),
		];

		if( ! empty( $row['query_id'] ) ) {

			$query_url = add_query_arg( [
				'page'         => 'jet-engine-query',
				'query_action' => 'edit',
				'id'           => absint( $row['query_id'] ),
			], admin_url( 'admin.php' ) );

			$items[] = $this->data_row_item( sprintf(
				'<a href="%1$s">%2$s</a>',
				$query_url, __( 'Query', 'jet-engine' )
			) );
		}

		if( ! empty( $row['lsting_url'] ) ) {
			$items[] = $this->data_row_item( sprintf(
				'<a href="%1$s">%2$s</a>',
				$row['lsting_url'], __( 'Base Listing', 'jet-engine' )
			) );
		}

		$result .= $this->data_row_content( implode( '|', $items ) );

		return $result;
	}

	/**
	 * Returns items URL for give slug
	 *
	 * @param  string $slug [description]
	 * @return [type]       [description]
	 */
	public static function get_items_url( $slug = '' ) {
		return add_query_arg(
			[
				'page' => 'jet-cct-' . $slug,
			],
			admin_url( 'admin.php' )
		);
	}

	/**
	 * Get realted tutorials
	 *
	 * @return array
	 */
	public function get_tuts() {
		return [
			[
				'url'   => 'https://crocoblock.com/knowledge-base/features/custom-content-type/',
				'label' => 'Custom Content Types Overview',
			],
		];
	}
}
