<?php
/**
 * Radio filter class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Smart_Filters_Radio_Filter' ) ) {
	/**
	 * Define Jet_Smart_Filters_Radio_Filter class
	 */
	class Jet_Smart_Filters_Radio_Filter extends Jet_Smart_Filters_Filter_Base {

		/**
		 * Get provider name
		 */
		public function get_name() {

			return __( 'Radio', 'jet-smart-filters' );
		}

		/**
		 * Get provider ID
		 */
		public function get_id() {

			return 'radio';
		}

		/**
		 * Get icon URL
		 */
		public function get_icon_url() {

			return jet_smart_filters()->plugin_url( 'admin/assets/img/filter-types/radio.png' );
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

			// additional settings
			$search_enabled   = isset( $args['search_enabled'] ) ? $args['search_enabled'] : false;
			$less_items_count = isset( $args['less_items_count'] ) ? $args['less_items_count'] : false;
			$scroll_height    = isset( $args['scroll_height'] ) ? $args['scroll_height'] : false;
			$dropdown_enabled = isset( $args['dropdown_enabled'] ) ? filter_var( $args['dropdown_enabled'], FILTER_VALIDATE_BOOLEAN ) : false;

			if ( ! $filter_id ) {
				return false;
			}

			$source                  = get_post_meta( $filter_id, '_data_source', true );
			$use_exclude_include     = get_post_meta( $filter_id, '_use_exclude_include', true );
			$exclude_include_options = get_post_meta( $filter_id, '_data_exclude_include', true );
			$add_all_option          = filter_var( get_post_meta( $filter_id, '_add_all_option', true ), FILTER_VALIDATE_BOOLEAN );
			$all_option_label        = $add_all_option ? get_post_meta( $filter_id, '_all_option_label', true ) : false;
			$can_deselect            = filter_var( get_post_meta( $filter_id, '_ability_deselect_radio', true ), FILTER_VALIDATE_BOOLEAN );
			$options                 = array();
			$by_parents              = false;
			$query_type              = false;
			$query_var               = false;
			$current_value           = false;
			$predefined_value        = $this->get_predefined_value( $filter_id );

			switch ( $source ) {
				case 'taxonomies':
					$tax              = get_post_meta( $filter_id, '_source_taxonomy', true );
					$only_child       = filter_var( get_post_meta( $filter_id, '_only_child', true ), FILTER_VALIDATE_BOOLEAN );
					$show_empty_terms = filter_var( get_post_meta( $filter_id, '_show_empty_terms', true ), FILTER_VALIDATE_BOOLEAN );
					$custom_query_var = $this->get_custom_query_var( $filter_id );
					$terms_orderby    = get_post_meta( $filter_id, '_terms_orderby', true );
					$terms_order      = get_post_meta( $filter_id, '_terms_order', true );
					$terms_meta_key   = '';

					if ( in_array( $terms_orderby, array( 'meta_value', 'meta_value_num' ) ) ) {
						$terms_meta_key = get_post_meta( $filter_id, '_terms_orderby_meta_value', '' );
					}

					if ( ! isset( $args['ignore_parents'] ) || true !== $args['ignore_parents'] ) {
						$by_parents = get_post_meta( $filter_id, '_group_by_parent', true );
						$by_parents = filter_var( $by_parents, FILTER_VALIDATE_BOOLEAN );
					}

					if ( true === $by_parents ) {
						$options = jet_smart_filters()->data->get_terms_objects( $tax, $only_child, array(
							'hide_empty' => ! $show_empty_terms,
							'orderby'    => ! empty( $terms_orderby ) ? $terms_orderby : 'name',
							'order'      => ! empty( $terms_order ) ? $terms_order : 'ASC',
							'meta_key'   => $terms_meta_key
						) );
					} else {
						$options = jet_smart_filters()->data->get_terms_for_options( $tax, $only_child, array(
							'hide_empty' => ! $show_empty_terms,
							'orderby'    => ! empty( $terms_orderby ) ? $terms_orderby : 'name',
							'order'      => ! empty( $terms_order ) ? $terms_order : 'ASC',
							'meta_key'   => $terms_meta_key
						) );
					}

					if ( is_category() || is_tag() || is_tax( $tax ) ) {
						$current_value = get_queried_object_id();
					}

					$query_type = 'tax_query';
					$query_var  = $tax;

					if ( $custom_query_var ) {
						$query_type = 'meta_query';
						$query_var  = $custom_query_var;
					}

					break;

				case 'posts':
					$post_type  = get_post_meta( $filter_id, '_source_post_type', true );
					$posts_args = apply_filters( 'jet-smart-filters/filters/posts-source/args', array(
						'post_type' => $post_type,
						'post_status' => 'publish',
						'posts_per_page' => -1
					) );

					$posts      = get_posts( $posts_args );
					$query_type = 'meta_query';
					$query_var  = get_post_meta( $filter_id, '_query_var', true );

					if ( ! empty( $posts ) ) {
						$options = wp_list_pluck( $posts, 'post_title', 'ID' );
					}

					break;

				case 'custom_fields':
					$custom_field   = get_post_meta( $filter_id, '_source_custom_field', true );
					$get_from_field = get_post_meta( $filter_id, '_source_get_from_field_data', true );
					$get_from_field = filter_var( $get_from_field, FILTER_VALIDATE_BOOLEAN );

					if ( $get_from_field ) {
						$options = jet_smart_filters()->data->get_choices_from_field_data( array(
							'field_key' => $custom_field,
							'source'    => get_post_meta( $filter_id, '_custom_field_source_plugin', true ),
						) );
					} else {
						$options = get_post_meta( get_the_ID(), $custom_field, true );

						if ( ! is_array( $options ) ) {
							$options = jet_smart_filters()->data->get_options_by_field_key( $custom_field );
						} else {
							$options = jet_smart_filters()->data->maybe_parse_repeater_options( $options );
						}
					}

					$query_type = 'meta_query';
					$query_var  = get_post_meta( $filter_id, '_query_var', true );
					break;

				case 'cct':
					$query_type = 'meta_query';
					$query_var  = get_post_meta( $filter_id, '_query_var', true );
					$options    = jet_smart_filters()->data->get_choices_from_cct_data( $query_var );

					break;

				default:
					$options    = get_post_meta( $filter_id, '_source_manual_input', true );
					$options    = ! empty( $options ) ? $options : array();
					$options    = wp_list_pluck( $options, 'label', 'value' );
					$query_type = 'meta_query';
					$query_var  = get_post_meta( $filter_id, '_query_var', true );
					break;
			}

			$options = jet_smart_filters()->data->maybe_include_exclude_options( $use_exclude_include, $exclude_include_options, $options );

			if ( $all_option_label ) {
				if ( true === $by_parents ) {
					$all_option = (object) array( 
						'term_id' => 'all',
						'name' => htmlspecialchars( $all_option_label )
					);
				} else {
					$all_option = htmlspecialchars( $all_option_label );
				}

				$options = array( 'all' => $all_option ) + $options;
			}

			$options = apply_filters( 'jet-smart-filters/filters/filter-options', $options, $filter_id, $this );

			$result = array(
				'options'              => $options,
				'query_type'           => $query_type,
				'query_var'            => $query_var,
				'by_parents'           => $by_parents,
				'query_var_suffix'     => jet_smart_filters()->filter_types->get_filter_query_var_suffix( $filter_id ),
				'content_provider'     => $content_provider,
				'additional_providers' => $additional_providers,
				'apply_type'           => $apply_type,
				'apply_on'             => $apply_on,
				'filter_id'            => $filter_id,
				'scroll_height'        => $scroll_height,
				'accessibility_label'  => $this->get_accessibility_label( $filter_id )
			);

			if ( $can_deselect ) {
				$result['can_deselect'] = $can_deselect;
			}

			if ( $current_value ) {
				$result['current_value'] = $current_value;
			}

			if ( $search_enabled ) {
				$result['search_enabled']     = $search_enabled;
				$result['search_placeholder'] = isset( $args['search_placeholder'] ) ? $args['search_placeholder'] : __( 'Search...', 'jet-smart-filters' );
			}

			if ( $less_items_count ) {
				$result['less_items_count'] = $less_items_count;
				$result['more_text']        = isset( $args['more_text'] ) ? $args['more_text'] : __( 'More', 'jet-smart-filters' );
				$result['less_text']        = isset( $args['less_text'] ) ? $args['less_text'] : __( 'Less', 'jet-smart-filters' );
			}

			if ( $dropdown_enabled ) {
				$result['dropdown_enabled']           = $dropdown_enabled;
				$result['dropdown_placeholder']       = isset( $args['dropdown_placeholder'] ) ? $args['dropdown_placeholder'] : __( 'Select some options', 'jet-smart-filters' );
				$result['dropdown_apply_button']      = isset( $args['dropdown_apply_button'] ) ? filter_var( $args['dropdown_apply_button'], FILTER_VALIDATE_BOOLEAN ) : false;
				$result['dropdown_apply_button_text'] = isset( $args['dropdown_apply_button_text'] ) ? $args['dropdown_apply_button_text'] : __( 'Apply', 'jet-smart-filters' );

				$dropdown_n_selected_enabled = isset( $args['dropdown_n_selected_enabled'] ) ? $args['dropdown_n_selected_enabled'] : false;
				if ( $dropdown_n_selected_enabled ) {
					$result['dropdown_n_selected_enabled'] = $dropdown_n_selected_enabled;
					$result['dropdown_n_selected_number']  = isset( $args['dropdown_n_selected_number'] ) ? $args['dropdown_n_selected_number'] : false;
					$result['dropdown_n_selected_text']    = isset( $args['dropdown_n_selected_text'] ) ? $args['dropdown_n_selected_text'] : false;
				}
			}

			if ( $by_parents ) {
				$result['collapsible'] = filter_var( get_post_meta( $filter_id, '_group_collapsible', true ), FILTER_VALIDATE_BOOLEAN );
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
