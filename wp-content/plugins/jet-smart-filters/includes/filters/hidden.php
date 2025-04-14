<?php
/**
 * Hidden filter class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Smart_Filters_Hidden_Filter' ) ) {
	/**
	 * Define Jet_Smart_Filters_Hidden_Filter class
	 */
	class Jet_Smart_Filters_Hidden_Filter {

		/**
		 * Get provider ID
		 */
		public function get_id() {

			return 'hidden';
		}

		/**
		 * Hidden filter container data attributes
		 */
		public function data_atts( $settings = array() ) {

			$output = '';

			$allKeysValid = true;
			foreach ( array( 'content_provider', 'argument_type', 'argument_name', 'argument_value' ) as $key ) {
				if ( empty( $settings[$key] ) ) {
					$allKeysValid = false;
					break;
				}
			}

			if ( ! $allKeysValid ) {
				return $output;
			}
			
			$argument_value = $settings['argument_value'];
			// processing shortcodes in value
			$argument_value = do_shortcode( $argument_value );
			// processing jet engine macro if Jet Engine plugin is active
			if ( function_exists( 'jet_engine' ) ) {
				$argument_value = jet_engine()->listings->macros->do_macros( $argument_value );
			}

			$data_atts = array();

			$data_atts['data-smart-filter']     = 'hidden';
			$data_atts['data-content-provider'] = $settings['content_provider'];
			if ( ! empty( $settings['query_id'] ) ) {
				$data_atts['data-query-id']     = $settings['query_id'];
			}
			$data_atts['data-apply-type']       = ! empty( $settings['apply_type'] ) ? $settings['apply_type'] : 'ajax';
			$data_atts['data-query-type']       = $settings['argument_type'] . '_query';
			$data_atts['data-query-var']        = $settings['argument_name'];
			$data_atts['data-predefined-value'] = $argument_value;

			$this->prepare_tax_data( $data_atts );

			foreach ( $data_atts as $key => $value ) {
				$output .= sprintf( ' %1$s="%2$s"', $key, $value );
			}

			return $output;
		}

		public function prepare_tax_data( &$data_atts ) {

			if ( $data_atts['data-query-type'] !== 'tax_query' ) {
				return;
			}

			$term_val_type = 'slug';
			$term_val      = $data_atts['data-predefined-value'];
			$term_tax      = $data_atts['data-query-var'];

			$term = term_exists( $term_val, $term_tax );
			// if term_val is ID
			if ( ! $term ) {
				$term_val_type = 'id';
				$term = term_exists( (int) $term_val, $term_tax );
			}

			// term not exists
			if ( ! $term ) {
				$data_atts = array();

				return;
			}

			$data_atts['data-predefined-value'] = $term['term_id'];

			// if taxonomy term name type in URL is slug
			if ( jet_smart_filters()->settings->url_taxonomy_term_name === 'slug' ) {
				if ( $term_val_type === 'slug' ) {
					$data_atts['data-url-value'] = $term_val;
				} else {
					$term_object = get_term( $term_val, $term_tax );
					$data_atts['data-url-value'] = $term_object->slug;
				}
			}
		}
	}
}
