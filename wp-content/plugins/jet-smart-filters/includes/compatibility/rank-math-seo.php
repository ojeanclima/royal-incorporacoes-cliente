<?php
/**
 * Rank Math SEO compatibility class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Jet_Smart_Filters_Compatibility_Rank_Math_SEO class
 */
class Jet_Smart_Filters_Compatibility_Rank_Math_SEO {

	/**
	 * Constructor for the class
	 */
	function __construct() {

		// Disabling Rank Math SEO
		add_action( 'jet-smart-filters/seo/frontend/init-rule', function() {
			$disable_integration = apply_filters( 'jet-smart-filters/compatibility/rank_math/disable_integration', true );

			if ( $disable_integration ) {
				add_filter('rank_math/frontend/disable_integration', '__return_true');
			}
		} );
	}
}
