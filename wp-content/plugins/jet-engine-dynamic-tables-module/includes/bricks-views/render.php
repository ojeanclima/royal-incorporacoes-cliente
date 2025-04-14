<?php
/**
 * Bricks views manager
 */
namespace Jet_Engine_Dynamic_Tables\Bricks_Views;

/**
 * Define render class
 */
class Render {

	private $current_query;

	public function __construct() {
		
		add_action( 'jet-engine/data-tables/before-render', [ $this, 'set_query_on_render' ] );
		add_action( 'jet-engine/data-tables/after-render', [ $this, 'destroy_bricks_query' ] );

		add_action( 'jet-smart-filters/render/ajax/before', [ $this, 'set_query_on_filters_ajax' ] );

	}

	public function set_bricks_query( $table_id = 0, $settings = [] ) {

		if ( ! $table_id ) {
			$table_id = isset( $settings['table_id'] ) ? absint( $settings['table_id'] ) : 0;
		}

		if ( $table_id ) {
			$this->current_query = jet_engine()->bricks_views->listing->get_bricks_query( [
				'id'       => 'jet-engine-dynamic-tables',
				'settings' => $settings,
			] );
		}

	}

	public function set_query_on_filters_ajax() {
		$settings = isset( $_REQUEST['settings'] ) ? $_REQUEST['settings'] : [];
		$this->set_bricks_query( 0, $settings );
	}

	public function set_query_on_render( $render ) {
		$this->set_bricks_query( $render->get_settings( 'table_id' ), $render->get_settings() );
	}

	public function destroy_bricks_query() {
		if ( $this->current_query ) {
			$this->current_query->destroy();
		}
	}

}
