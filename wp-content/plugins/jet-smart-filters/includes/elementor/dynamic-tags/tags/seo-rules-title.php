<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Jet_Smart_Filters_Elementor_SEO_Rules_Title extends Elementor\Core\DynamicTags\Tag {

	public function get_name() {

		return 'jet-smart-filters-seo-title';
	}

	public function get_title() {

		return __( 'SEO Title', 'jet-smart-filters' );
	}

	public function get_group() {

		return Jet_Smart_Filters_Elementor_Dynamic_Tags_Module::JET_SMART_FILTERS_GROUP;
	}

	public function get_categories() {

		return array(
			Jet_Smart_Filters_Elementor_Dynamic_Tags_Module::TEXT_CATEGORY,
		);
	}

	public function is_settings_required() {

		return true;
	}

	public function render() {

		$current_title = jet_smart_filters()->seo->frontend->get_current_title();
		$fallback      = $this->get_settings( 'fallback' );

		if ( empty( $current_title ) ) {
			if ( $fallback ) {
				$current_title = $fallback;
			} else {
				// output placeholder text in edit mode if title and fallback are empty
				$is_edit_mode = \Elementor\Plugin::$instance->editor->is_edit_mode();

				if ( ! $is_edit_mode && defined( 'DOING_AJAX' ) && DOING_AJAX ) {
					if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'elementor_ajax' ) {
						$is_edit_mode = true;
					}
				}

				if ( $is_edit_mode ) {
					$current_title = __( 'SEO Title', 'jet-smart-filters' );
				}
			}
		}

		echo '<span';
		echo ' class="' . jet_smart_filters()->seo->frontend->title_class . '"';
		if ( $fallback ) {
			echo ' data-fallback="' . $fallback . '"';
		}
		echo '>';

			echo wp_kses_post( $current_title );

		echo '</span>';
	}
}
