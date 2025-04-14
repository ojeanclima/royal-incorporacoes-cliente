<?php
/**
 * Bricks Dynamic Data SEO_Rules_Description
 */

namespace Jet_Smart_Filters\Bricks_Views\Dynamic_Data;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class SEO_Rules_Description {

	public function get_name() {

		return 'seo-description';
	}

	public function get_title() {

		return __( 'SEO Description', 'jet-smart-filters' );
	}

	public function render() {

		$output = '';

		$output  = '<span class="' . jet_smart_filters()->seo->frontend->description_class . '">';
			$output .= wp_kses_post( jet_smart_filters()->seo->frontend->get_current_description() );
		$output .=  '</span>';

		return $output;
	}
}
