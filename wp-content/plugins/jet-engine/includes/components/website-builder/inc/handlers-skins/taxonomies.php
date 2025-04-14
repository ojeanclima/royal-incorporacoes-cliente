<?php
namespace Jet_Engine\Website_Builder\Handlers_Skins;

use \Jet_Engine\Website_Builder\Handler;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Taxonomies extends Base {

	/**
	 * Get skin ID
	 *
	 * @return string
	 */
	public function get_id() {
		return Handler::TAXONOMIES_ID;
	}

	/**
	 * Get skin title
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Taxonomies', 'jet-engine' );
	}

	/**
	 * Render single results row
	 *
	 * @param  array  $row Results data row.
	 * @return string
	 */
	public function skin_data_row( $row = [] ) {

		$result = '';

		$tax_edit = add_query_arg(
			[
				'page' => 'jet-engine-cpt-tax',
				'cpt_tax_action' => 'edit',
				'id' => $row['id'],
			],
			admin_url( 'admin.php' )
		);

		$post_type_posts = self::get_items_url( $row['slug'] );

		$result .= $this->data_row_title(
			$row['name'],
			sprintf( '<a href="%1$s">%2$s</a>', $tax_edit, __( 'Edit', 'jet-engine' ) )
		);

		$items = [
			$this->data_row_item( sprintf(
				'<a href="%1$s">%2$s</a>',
				$post_type_posts, __( 'Add/Edit terms', 'jet-engine' )
			) ),
		];

		$result .= $this->data_row_content( implode( '|', $items ) );

		return $result;
	}

	/**
	 * Get URL to items page of given tax by slug
	 * @return [type] [description]
	 */
	public static function get_items_url( $slug ) {
		return add_query_arg(
			[
				'taxonomy' => $slug,
			],
			admin_url( 'edit-tags.php' )
		);
	}
}
