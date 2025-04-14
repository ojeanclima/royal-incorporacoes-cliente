<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

/**
 * Elementor integrations
 */
class Jet_Smart_Filters_Elementor_Manager {

	public function __construct() {

		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
			return;
		}

		require jet_smart_filters()->plugin_path( 'includes/elementor/widgets.php' );
		jet_smart_filters()->widgets = new Jet_Smart_Filters_Widgets_Manager();

		$this->init_dynamic_tags();

		// will wait for the first filter widget, will work once, then the hook will be removed
		add_action( 'elementor/frontend/widget/before_render', array( $this, 'on_first_filter_widget' ) );
	}

	public function init_dynamic_tags() {

		$init_action = 'elementor/init';

		// Init a module early on Elementor Data Updater
		if ( is_admin() && ( isset( $_GET['elementor_updater'] ) || isset( $_GET['elementor_pro_updater'] ) ) ) {
			$init_action = 'elementor/documents/register';
		}

		add_action( $init_action, array( $this, 'init_dynamic_tags_module' ) );
	}

	public function init_dynamic_tags_module() {

		require jet_smart_filters()->plugin_path( 'includes/elementor/dynamic-tags/module.php' );
		new Jet_Smart_Filters_Elementor_Dynamic_Tags_Module();
	}

	// when the first filter widget appears, it will run the set_filters_used method
	// for correctly work "Dynamic Visibility" need to add styles before method render()
	public function on_first_filter_widget( $widget ) {

		if ( ! method_exists( $widget, 'get_categories' ) || ! in_array( jet_smart_filters()->widgets->get_category(), $widget->get_categories() ) ) {
			return;
		}

		jet_smart_filters()->set_filters_used();

		remove_action( 'elementor/frontend/widget/before_render', array( $this, 'on_first_filter_widget' ) );
	}
}
