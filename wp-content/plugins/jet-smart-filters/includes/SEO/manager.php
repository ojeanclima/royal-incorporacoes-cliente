<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

/**
 * JetSmartFilters SEO manager
 */
class Jet_Smart_Filters_SEO {

	public $is_enabled;

	/**
	 * Components
	 */
	public $sitemap;
	public $frontend;
	
	public function __construct() {

		// register components
		$this->register_seo_sitemap();
		$this->register_seo_frontend();
	}

	private function register_seo_sitemap() {

		require jet_smart_filters()->plugin_path( 'includes/SEO/sitemap.php' );

		$this->sitemap = new Jet_Smart_Filters_SEO_Sitemap();
	}

	private function register_seo_frontend() {

		require jet_smart_filters()->plugin_path( 'includes/SEO/frontend.php' );

		$this->frontend = new Jet_Smart_Filters_SEO_Frontend();
	}
}
