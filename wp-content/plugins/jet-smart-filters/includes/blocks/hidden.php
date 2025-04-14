<?php
/**
 * Hidden Filter
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Smart_Filters_Block_Hidden' ) ) {
	/**
	 * Define Jet_Smart_Filters_Block_Hidden class
	 */
	class Jet_Smart_Filters_Block_Hidden extends Jet_Smart_Filters_Block_Base {
		/**
		 * Returns block name
		 */
		public function get_name() {

			return 'hidden';
		}

		/**
		 * Return callback
		 */
		public function render_callback( $settings = array() ) {

			if ( empty( $settings['content_provider'] ) || $settings['content_provider'] === 'not-selected' ) {
				return $this->is_editor() ? __( 'Please select a provider', 'jet-smart-filters' ) : false;
			}

			jet_smart_filters()->set_filters_used();

			$is_editor          = defined( 'REST_REQUEST' ) && REST_REQUEST && isset( $_GET['context'] ) && $_GET['context'] === 'edit';
			$base_class         = 'jet-smart-filters-' . $this->get_name();
			$hidden_filter_type = jet_smart_filters()->filter_types->get_filter_types( 'hidden' );
			$data_atts          = $hidden_filter_type->data_atts( $settings );

			ob_start();

			printf(
				'<div class="%1$s jet-filter" data-is-block="jet-smart-filters/%2$s">',
				$base_class,
				$this->get_name()
			);

			include jet_smart_filters()->get_template( 'filters/hidden.php' );

			echo '</div>';

			$filter_layout = ob_get_clean();

			return $filter_layout;
		}
	}
}
