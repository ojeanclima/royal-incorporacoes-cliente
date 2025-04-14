<?php
namespace Jet_Engine_Dynamic_Tables\Filters;

class Manager {

	private $provider_id = 'jet-data-table';

	public function __construct() {
		add_action( 'jet-smart-filters/providers/register', array( $this, 'register_provider_for_filters' ) );
		add_filter( 'jet-engine/query-builder/filters/allowed-providers', array( $this, 'register_provider_for_query_builder' ) );
		add_filter( 'jet-smart-filters/blocks/allowed-providers', array( $this, 'register_provider_for_blocks_filters' ) );
	}

	/**
	 * Register new provider for the filters
	 *
	 * @return [type] [description]
	 */
	public function register_provider_for_filters( $providers_manager ) {
		$providers_manager->register_provider(
			'\Jet_Engine_Dynamic_Tables\Filters\Provider',
			JET_ENGINE_DYNAMIC_TABLES_PATH . 'includes/filters/provider.php'
		);
	}

	public function register_provider_for_query_builder( $providers ) {
		$providers[] = $this->provider_id;
		return $providers;
	}

	public function register_provider_for_blocks_filters( $providers ) {
		$providers[ $this->provider_id ] = __( 'JetEngine Dynamic Table', 'jet-engine' );
		return $providers;
	}

}
