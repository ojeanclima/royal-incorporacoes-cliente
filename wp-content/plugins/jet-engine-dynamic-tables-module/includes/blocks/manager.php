<?php
namespace Jet_Engine_Dynamic_Tables\Blocks;

use Jet_Engine_Dynamic_Tables\Plugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Manager {

	public function __construct() {
		add_action( 'jet-engine/blocks-views/register-block-types', array( $this, 'register_blocks' ), 10 );
		add_action( 'jet-engine/blocks-views/editor-script/after', array( $this, 'register_blocks_script' ), 10 );
		add_filter( 'jet-engine/blocks-views/editor/config', array( $this, 'localize_tables_list' ) );
	}

	public function register_blocks( $blocks_manager ) {
		$blocks_manager->register_block_type( new Table_Block() );
	}

	public function register_blocks_script() {
		wp_enqueue_script(
			'jet-engine-dynamic-table',
			JET_ENGINE_DYNAMIC_TABLES_URL . 'assets/js/blocks/blocks.js',
			array(),
			JET_ENGINE_DYNAMIC_TABLES_VERSION,
			true
		);
	}

	public function localize_tables_list( $config ) {

		$config['tablesList'] = Plugin::instance()->data->get_tables_for_options( 'blocks' );
		$config['atts']['dynamicTable'] = jet_engine()->blocks_views->block_types->get_block_atts( 'dynamic-table' );

		return $config;
	}

}
