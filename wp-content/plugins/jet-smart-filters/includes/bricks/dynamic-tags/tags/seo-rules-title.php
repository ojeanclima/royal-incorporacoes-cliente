<?php
/**
 * Bricks Dynamic Data SEO_Rules_Title
 */

namespace Jet_Smart_Filters\Bricks_Views\Dynamic_Data;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class SEO_Rules_Title {

	public function get_name() {

		return 'seo-title';
	}

	public function get_title() {

		return __( 'SEO Title', 'jet-smart-filters' );
	}

	public function render() {

		$output = '';

		$output  = '<span class="' . jet_smart_filters()->seo->frontend->title_class . '">';
			$output .= wp_kses_post( jet_smart_filters()->seo->frontend->get_current_title() );
		$output .=  '</span>';

		return $output;
	}
}
