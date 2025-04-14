<?php
/**
 * Alphabet filter class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Smart_Filters_Alphabet_Filter' ) ) {
	/**
	 * Define Jet_Smart_Filters_Alphabet_Filter class
	 */
	class Jet_Smart_Filters_Alphabet_Filter extends Jet_Smart_Filters_Filter_Base {
		/**
		 * Get provider name
		 */
		public function get_name() {

			return __( 'Alphabet', 'jet-smart-filters' );
		}

		/**
		 * Get provider ID
		 */
		public function get_id() {

			return 'alphabet';
		}

		/**
		 * Get icon URL
		 */
		public function get_icon_url() {

			return jet_smart_filters()->plugin_url( 'admin/assets/img/filter-types/alphabet.png' );
		}

		/**
		 * Get provider wrapper selector
		 */
		public function get_scripts() {

			return false;
		}

		/**
		 * Prepare filter template argumnets
		 */
		public function prepare_args( $args ) {

			$filter_id            = $args['filter_id'];
			$content_provider     = isset( $args['content_provider'] ) ? $args['content_provider'] : false;
			$additional_providers = isset( $args['additional_providers'] ) ? $args['additional_providers'] : false;
			$apply_type           = isset( $args['apply_type'] ) ? $args['apply_type'] : false;
			$apply_on             = isset( $args['apply_on'] ) ? $args['apply_on'] : false;

			if ( ! $filter_id ) {
				return false;
			}

			$query_type       = 'alphabet';
			$query_var        = '';
			$behavior         = get_post_meta( $filter_id, '_alphabet_behavior', true );
			$can_deselect     = filter_var( get_post_meta( $filter_id, '_alphabet_radio_deselect', true ), FILTER_VALIDATE_BOOLEAN );
			$options          = array_map( 'trim', explode( ',', get_post_meta( $filter_id, '_alphabet_options', true ) ) );
			$predefined_value = $this->get_predefined_value( $filter_id );

			$result = array(
				'options'              => $options,
				'query_type'           => $query_type,
				'query_var'            => $query_var,
				'query_var_suffix'     => jet_smart_filters()->filter_types->get_filter_query_var_suffix( $filter_id ),
				'content_provider'     => $content_provider,
				'additional_providers' => $additional_providers,
				'apply_type'           => $apply_type,
				'apply_on'             => $apply_on,
				'filter_id'            => $filter_id,
				'behavior'             => $behavior,
				'accessibility_label'  => $this->get_accessibility_label( $filter_id )
			);

			if ( $can_deselect ) {
				$result['can_deselect'] = $can_deselect;
			}

			if ( $predefined_value !== false ) {
				$result['predefined_value'] = $predefined_value;
			}

			return $result;
		}

		public function additional_filter_data_atts( $args ) {

			$additional_filter_data_atts = array();

			if ( ! empty( $args['can_deselect'] ) ) $additional_filter_data_atts['data-can-deselect'] = $args['can_deselect'];

			return $additional_filter_data_atts;
		}
	}
}
