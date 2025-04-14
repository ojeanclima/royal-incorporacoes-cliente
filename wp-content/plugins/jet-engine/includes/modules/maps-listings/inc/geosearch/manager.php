<?php
namespace Jet_Engine\Modules\Maps_Listings\Geosearch;

use Jet_Engine\Modules\Maps_Listings\Module;

class Manager {

	public function __construct() {

		add_action( 'init', array( $this, 'init' ), 100 );

	}

	public function init() {
		
		require jet_engine()->modules->modules_path( 'maps-listings/inc/geosearch/controls/base.php' );
		require jet_engine()->modules->modules_path( 'maps-listings/inc/geosearch/controls/posts.php' );
		require jet_engine()->modules->modules_path( 'maps-listings/inc/geosearch/controls/users.php' );
		require jet_engine()->modules->modules_path( 'maps-listings/inc/geosearch/controls/terms.php' );

		new Controls\Posts();
		new Controls\Users();
		new Controls\Terms();

		add_action( 'jet-engine/query-builder/editor/before-enqueue-scripts', array( $this, 'geosearch_map_component' ), 0 );

		require jet_engine()->modules->modules_path( 'maps-listings/inc/geosearch/query/base.php' );
		require jet_engine()->modules->modules_path( 'maps-listings/inc/geosearch/query/posts.php' );
		require jet_engine()->modules->modules_path( 'maps-listings/inc/geosearch/query/users.php' );
		require jet_engine()->modules->modules_path( 'maps-listings/inc/geosearch/query/terms.php' );
		require jet_engine()->modules->modules_path( 'maps-listings/inc/geosearch/query/posts-custom-storage.php' );

		new Query\Posts();
		new Query\Users();
		new Query\Terms();
		new Query\Posts_Custom_Storage();

		do_action( 'jet-engine/maps-listing/geosearch/init' );

	}

	public function geosearch_map_component() {

		$provider = Module::instance()->providers->get_active_map_provider();

		$provider->register_public_assets();
		$provider->public_assets( null, array( 'marker_clustering' => false ), null );

		wp_enqueue_script(
			'jet-engine-geosearch-map',
			jet_engine()->plugin_url( 'includes/modules/maps-listings/assets/js/admin/geosearch-component.js' ),
			array( 'wp-api-fetch' ),
			jet_engine()->get_version(),
			true
		);

		wp_enqueue_style(
			'jet-engine-map-geosearch-component',
			jet_engine()->plugin_url( 'includes/modules/maps-listings/assets/css/geosearch-component.css' ),
			array(),
			jet_engine()->get_version()
		);

		wp_localize_script( 'jet-engine-geosearch-map', 'JetEngineGeoSearch', array(
			'api' => jet_engine()->api->get_route( 'get-map-point-data' ),
			'label' => __( 'Select location', 'jet-engine' ),
			'description' => __( 'Select point on the map to get values around', 'jet-engine' ),
			'help' =>  __( '* You need to pick a point on the map to search around or set coordinates manually/via a macro', 'jet-engine' ),
			'helpMacro' => __( '* Map is currently disabled because macro is selected as a point coordinates source', 'jet-engine' ),
		) );
	}

}
