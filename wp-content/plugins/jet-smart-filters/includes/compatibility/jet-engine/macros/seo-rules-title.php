<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Jet_Smart_Filters_Macros_SEO_Rules_Title extends Jet_Engine_Base_Macros {

	public function macros_tag() {

		return 'jsf_seo_title';
	}

	public function macros_name() {

		return __( 'JetSmartFilters SEO Title', 'jet-smart-filters' );
	}

	public function macros_args() {

		return array(
			'fallback' => array(
				'label'   => __( 'Fallback', 'jet-smart-filters' ),
				'default' => '',
				'type'    => 'text',
			)
		);
	}

	public function macros_callback( $args = array() ) {

		$output        = '';
		$current_title = jet_smart_filters()->seo->frontend->get_current_title();
		$fallback      = $args['fallback'];

		if ( empty( $current_title ) && ! empty( $fallback ) ) {
			$current_title = $fallback;
		}

		$output  = '<span';
		$output .= ' class="' . jet_smart_filters()->seo->frontend->title_class . '"';
		if ( $fallback ) {
			$output .= ' data-fallback="' . $fallback . '"';
		}
		$output .= '>';
			$output .= wp_kses_post( $current_title );
		$output .= '</span>';

		return $output;
	}
}
