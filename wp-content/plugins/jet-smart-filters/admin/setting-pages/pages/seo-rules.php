<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

final class Jet_Smart_Filters_Admin_Setting_Page_Seo_Rules extends Jet_Smart_Filters_Admin_Setting_Page_Base {

	public function get_page_slug() {

		return 'jet-smart-filters-seo-rules-settings';
	}

	public function get_page_name() {

		return esc_html__( 'SEO Rules Settings (beta)', 'jet-smart-filters' );
	}

	public function enqueue_module_assets() {
		
		parent::enqueue_module_assets();

		jet_smart_filters()->print_x_templates( 'jet-smart-filters-seo-rule-item', 'admin/setting-pages/templates/components/seo-rule-item.php' );
		jet_smart_filters()->print_x_templates( 'jet-smart-filters-repeater', 'admin/setting-pages/templates/components/repeater.php' );
		jet_smart_filters()->print_x_templates( 'jet-smart-filters-macro-input', 'admin/setting-pages/templates/components/macro-input.php' );
	}

	public function page_templates( $templates = array(), $page = false, $subpage = false ) {

		$templates['jet-smart-filters-seo-rules-settings'] = jet_smart_filters()->plugin_path( 'admin/setting-pages/templates/seo-rules-settings.php' );

		return $templates;
	}
}
