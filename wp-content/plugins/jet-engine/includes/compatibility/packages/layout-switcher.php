<?php
/**
 * Relevanssi compatibility package
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Layout_Switcher_Package' ) ) {

	/**
	 * Define Jet_Engine_Relevanssi_Package class
	 */
	class Jet_Engine_Layout_Switcher_Package {

		public function __construct() {
			//https://github.com/Crocoblock/issues-tracker/issues/14298
			//temporary fix for the issue with layout switcher
			add_filter( 'jet-engine/listing/dynamic-widget/has-inner-wrapper', array( $this, 'ensure_wrapper' ), 10, 2 );
		}

		public function ensure_wrapper( $has_wrapper, $widget ) {
			if ( $widget->get_name() === 'jet-listing-grid' ) {
				return true;
			}

			return $has_wrapper;
		}

	}

}

new Jet_Engine_Layout_Switcher_Package();
