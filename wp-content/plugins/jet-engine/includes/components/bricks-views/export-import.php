<?php
/**
 * Bricks views manager
 */
namespace Jet_Engine\Bricks_Views;

class Export_Import {

	public function __construct() {
		add_action( 'jet-engine/listings/document/content', [ $this, 'export_listing_content' ], 10, 2 );
		add_filter( 'jet-engine/dashboard/import/listing-item', [ $this, 'import_listing_content' ], 10, 2 );
	}

	/**
	 * Process bricks content export
	 * @param  [type] $content [description]
	 * @param  [type] $listing [description]
	 * @return [type]          [description]
	 */
	public function export_listing_content( $content, $listing ) {

		if ( 'bricks' === $listing->get_settings( '_listing_type' ) && defined( 'BRICKS_DB_PAGE_CONTENT' ) ) {
			$content = get_post_meta( $listing->get_main_id(), BRICKS_DB_PAGE_CONTENT, true );
		}

		return $content;
	}

	/**
	 * Process importing content of listing item
	 * 
	 * @param  array  $prepared_listing [description]
	 * @param  array  $raw_listing      [description]
	 * @return [type]                   [description]
	 */
	public function import_listing_content( $prepared_listing = [], $raw_listing = [] ) {

		if ( ! empty( $raw_listing['type'] ) && 'bricks' === $raw_listing['type'] && defined( 'BRICKS_DB_PAGE_CONTENT' ) ) {
			$prepared_listing['meta_input'][ BRICKS_DB_PAGE_CONTENT ] = $raw_listing['content'] ?? [];
		}

		return $prepared_listing;

	}

}
