<?php
/**
 * Woocommerce compatibility class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Jet_Smart_Filters_Compatibility_Woocommerce class
 */
class Jet_Smart_Filters_Compatibility_WC {
	/**
	 * Constructor for the class
	 */
	function __construct() {

		add_action( 'jet-smart-filters/referrer/request', array( $this, 'setup_wc_product' ) );
		add_filter( 'jet-engine/listing/grid/posts-query-args', array( $this, 'wc_modify_sort_query_args' ), 20 );

		if ( jet_smart_filters()->settings->wc_hide_out_of_stock_variations ) {
			add_filter( 'jet-smart-filters/query/final-query', array( $this, 'hide_out_of_stock_variations_modify_query' ) );
			add_filter( 'jet-smart-filters/filters/indexed-data', array( $this, 'hide_out_of_stock_variations_indexed' ), 10, 2 );
		}
	}

	public function setup_wc_product() {

		global $wp;

		if ( ! function_exists( 'wc_setup_product_data' ) ) {
			return;
		}

		if ( empty( $wp->query_vars['post_type'] ) || 'product' !== $wp->query_vars['post_type'] ) {
			return;
		}

		if ( empty( $wp->query_vars['product'] ) ) {
			return;
		}

		$posts = get_posts( [
			'post_type' => 'product',
			'name' => $wp->query_vars['product'],
			'posts_per_page' => 1
		] );

		if ( empty( $posts ) ) {
			return;
		}

		global $post;
		$post = $posts[0];

		wc_setup_product_data( $post );
	}

	public function wc_modify_sort_query_args( $args ) {

		if ( ! isset( $args['jet_smart_filters'] ) || ! jet_smart_filters()->query->get_query_args() ) {
			return $args;
		}

		if ( isset( $args['wc_query'] ) ) {
			if ( isset( $args['orderby'] ) && isset( $args['order'] ) ) {
				$ordering_args = WC()->query->get_catalog_ordering_args( $args['orderby'], $args['order'] );

				// Prevent rewrite the order only to DESC if the orderby is relevance.
				if ( 'relevance' === $args['orderby'] && ! empty( $args['order'] ) ) {
					$ordering_args['order'] = $args['order'];
				}
			} else {
				$ordering_args = WC()->query->get_catalog_ordering_args();
			}

			$args['orderby'] = $ordering_args['orderby'];
			$args['order']   = $ordering_args['order'];

			if ( $ordering_args['meta_key'] ) {
				$args['meta_key'] = $ordering_args['meta_key'];
			}
		}

		return $args;
	}

	public function hide_out_of_stock_variations_modify_query( $query ) {

		if ( ! isset( $query['tax_query'] ) ) {
			return $query;
		}

		foreach ( $query['tax_query'] as $key => $item ) {

			if ( isset( $item['taxonomy'] ) && taxonomy_is_product_attribute( $item['taxonomy'] ) ) {

				if ( is_array( $item['terms'] ) ) {
					$terms = implode( ',', $item['terms'] );
				} else {
					$terms = $item['terms'];
				}

				global $wpdb;

				$post_in = $wpdb->get_col(
					"SELECT product_or_parent_id 
					FROM {$wpdb->prefix}wc_product_attributes_lookup 
					WHERE term_id IN ( {$terms} )
					AND in_stock = '1'"
				);

				if ( ! empty( $post_in ) && isset( $query['post__in'] ) ) {
					$query['post__in'] = array_unique( array_merge( $query['post__in'], $post_in ) );
				} elseif ( ! empty( $post_in ) ) {
					$query['post__in'] = $post_in;
				}

				if ( empty( $query['post__in'] ) ) {
					$query['post__in'] = array( 0 );
				}
			}
		}

		return $query;
	}

	public function hide_out_of_stock_variations_indexed( $indexedData, $props ) {

		if ( empty( $indexedData['tax_query'] ) || ! is_array( $indexedData['tax_query'] ) ) {
			return $indexedData;
		}

		$variations_indexing_data = array();

		foreach ( $indexedData['tax_query'] as $key => $value ) {
			if ( taxonomy_is_product_attribute( $key ) ) {
				$variations_indexing_data[$key] = array_keys( $value );
			}
		}

		if ( empty( $variations_indexing_data ) ) {
			return $indexedData;
		}

		global $wpdb;

		$conditions = array();
		foreach ( $variations_indexing_data as $taxonomy => $term_ids ) {
			$term_ids     = array_map( 'intval', $term_ids);
			$conditions[] = "( taxonomy IN ( '$taxonomy' ) AND term_id IN ( " . implode(',', $term_ids) . " ) )";
		}

		$sql = "
			SELECT taxonomy, term_id, COUNT(*) AS count
				FROM (
					SELECT taxonomy, term_id
						FROM {$wpdb->prefix}wc_product_attributes_lookup
							WHERE in_stock != 0
								AND ( " . implode( ' OR ', $conditions ) . " )
								AND product_or_parent_id IN ( " . implode( ',', $props['queried_ids'] ) . " )
							GROUP BY
								taxonomy,
								product_or_parent_id,
								term_id
				) AS subquery
					GROUP BY
						taxonomy,
						term_id";
		$result = $wpdb->get_results( $sql, ARRAY_A );

		// reset previous indexer values
		foreach ( $variations_indexing_data as $taxonomy => $term_ids ) {
			foreach ( $term_ids as $term_id ) {
				$indexedData['tax_query'][$taxonomy][$term_id] = 0;
			}
		}

		// set new index indexer
		foreach ( $result as $row ) {
			if ( isset( $indexedData['tax_query'][$row['taxonomy']][$row['term_id']] ) ) {
				$indexedData['tax_query'][$row['taxonomy']][$row['term_id']] = $row['count'];
			}
		}

		return $indexedData;
	}
}
