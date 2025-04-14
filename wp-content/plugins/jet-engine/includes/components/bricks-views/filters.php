<?php

namespace Jet_Engine\Bricks_Views;

use Bricks\Database;
use Jet_Engine\Query_Builder\Manager as Query_Manager;

class Filters {
	public function __construct() {
		add_action( 'jet-engine/bricks-views/query-builder/on-query', array( $this, 'maybe_set_query_props' ), 10, 2 );
		add_action( 'jet-engine/query-builder/query/after-query-setup', array( $this, 'maybe_setup_filter' ), 11 );
	}

	public function maybe_set_query_props( $query, $element_id ) {
		if ( ! $query ) {
			return;
		}

		$query_id = $query->query_id ?? '';

		if ( ! $query_id && Query_Manager::instance()->listings->filters->is_filters_request( $query ) ) {
			$query_id = jet_smart_filters()->query->get_current_provider( 'query_id' );
		}

		$provider              = 'bricks-query-loop';
		$post_id               = Database::$page_data['original_post_id'] ?? Database::$page_data['preview_or_post_id'];
		$template_content_type = Database::$active_templates['content_type'] ?? '';

		if ( $template_content_type === 'archive' ) {
			$post_id = Database::$active_templates['content'];
		}

		if ( $template_content_type === 'search' ) {
			$post_id = Database::$active_templates['search'];
		}

		// Setup props for the pager
		jet_smart_filters()->query->set_props(
			$provider,
			array(
				'found_posts'   => $query->get_items_total_count(),
				'max_num_pages' => $query->get_items_pages_count(),
				'page'          => $query->get_current_items_page(),
				'query_type'    => $query->get_query_type(),
				'query_id'      => $query->id,
				'query_meta'    => $query->get_query_meta(),
			),
			$query_id
		);

		// Store settings to localize it by SmartFilters later
		jet_smart_filters()->providers->store_provider_settings(
			$provider,
			array(
				'filtered_post_id' => $post_id,
				'element_id'       => $element_id,
			),
			$query_id
		);

		// Store current query to allow indexer to get correct posts count for current query
		jet_smart_filters()->query->store_provider_default_query(
			$provider,
			$query->get_query_args(),
			$query_id
		);
	}

	/**
	 * Setup filtered data if it was filters request
	 *
	 * @param  [type] $query [description]
	 *
	 * @return [type]        [description]
	 */
	public function maybe_setup_filter( $query ) {
		$remove_hook = false;

		// Get filtered query
		if ( Query_Manager::instance()->listings->filters->is_filters_request( $query ) ) {
			$remove_hook = true;

			add_filter( 'jet-smart-filters/render/ajax/data', function ( $data ) use ( $query ) {

				$count = $query->get_items_total_count();

				$data['fragments']["[data-je-qr-count='$query->id']"] = $count;

				if ( ! empty( $query->query_id ) ) {
					$data['fragments']["[data-je-qr-count='$query->query_id']"] = $count;
				}

				return $data;
			} );
		}

		if ( $remove_hook ) {
			remove_action( 'jet-engine/query-builder/query/after-query-setup', array( $this, 'maybe_setup_filter' ) );
		}
	}
}
