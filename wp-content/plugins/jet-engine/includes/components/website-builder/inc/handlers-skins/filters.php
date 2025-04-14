<?php
namespace Jet_Engine\Website_Builder\Handlers_Skins;

use \Jet_Engine\Website_Builder\Handler;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Filters extends Base {

	/**
	 * Get skin ID
	 *
	 * @return string
	 */
	public function get_id() {
		return Handler::FILTERS_ID;
	}

	/**
	 * Get skin title
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Filters', 'jet-engine' );
	}

	/**
	 * Render single results row
	 *
	 * @param  array  $row Results data row.
	 * @return string
	 */
	public function skin_data_row( $row = [] ) {

		$result = '';

		$filter_edit = admin_url( 'admin.php?page=jet-smart-filters#/' . $row['id'] );

		$result .= $this->data_row_title(
			$row['name'],
			sprintf( '<a href="%1$s">%2$s</a>', $filter_edit, __( 'Edit', 'jet-engine' ) )
		);

		return $result;
	}

	/**
	 * Get realted tutorials
	 *
	 * @return array
	 */
	public function get_tuts() {
		return [
			[
				'url'   => 'https://crocoblock.com/knowledge-base/jetsmartfilters/how-to-use-filters-with-listing-grid/',
				'label' => 'How to Use Filters with the Listing Grid Widget',
			],
			[
				'url'   => 'https://crocoblock.com/knowledge-base/plugins/jetsmartfilters/',
				'label' => 'Filters Knowledge Base',
			],
		];
	}

}
