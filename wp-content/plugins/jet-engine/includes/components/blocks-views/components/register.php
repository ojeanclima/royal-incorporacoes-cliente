<?php
namespace Jet_Engine\Blocks_Views\Components;

class Register {

	private $_registered_blocks = [];
	private $_initialized = false;

	public function __construct() {
		add_action( 'jet-engine/listings/components/register-component-elements', [ $this, 'register_component_blocks' ] );
		
	}

	public function init() {
		
		$this->_initialized = true;

		add_filter( 'jet-engine/blocks-views/editor-data', [ $this, 'add_editor_data' ] );
		add_action( 'jet-engine/blocks-views/render-block-preview', [ $this, 'setup_preview_state' ] );

		// Tweak for external builders compatibility
		add_action( 'elementor/preview/enqueue_styles', function() {
			
			// For some reason these 2 blocks styles doesn't enqueued in the preview mode
			// Most of other blocks styles are enqueued automatically
			wp_enqueue_style( 'wp-block-columns' );
			wp_enqueue_style( 'wp-block-column' );

		} );

	}

	/**
	 * Setup preview state for given block
	 * 
	 * @param  [type] $block      [description]
	 * @param  [type] $attributes [description]
	 * @return [type]             [description]
	 */
	public function setup_preview_state( $block ) {

		$post_id = ! empty( $_REQUEST['post_id'] ) ? absint( $_REQUEST['post_id'] ) : false;
		
		if ( ! $post_id || ! jet_engine()->listings->components->is_component( $post_id ) ) {
			return;
		}

		$component = jet_engine()->listings->components->get( $post_id, 'id' );

		jet_engine()->listings->components->state->set( $component->get_default_state() );

	}

	/**
	 * Add block editor data required for compnents registration
	 * 
	 * @param array $data [description]
	 */
	public function add_editor_data( $data = [] ) {

		if ( ! empty( $this->_registered_blocks ) ) {
			$data['blockComponents'] = $this->_registered_blocks;
		}
		
		return $data;
	}

	/**
	 * Register appropriate blocks for the components
	 * 
	 * @param  [type] $component [description]
	 * @return [type]            [description]
	 */
	public function register_component_blocks( $component ) {

		if ( ! $component->is_view_supported( 'blocks' ) ) {
			return;
		}

		if ( ! class_exists( '\Jet_Engine\Blocks_Views\Components\Block_Type' ) ) {
			require jet_engine()->blocks_views->component_path( 'components/block-type.php' );
		}

		$this->add_registered_block( new Block_Type( $component ) );

		if ( ! $this->_initialized ) {
			$this->init();
		}

	}

	/**
	 * save registerd block info to register in JS
	 * 
	 * @param [type] $block [description]
	 */
	public function add_registered_block( $block ) {
		$this->_registered_blocks[] = [
			'name' => $block->get_block_name(),
			'title' => $block->get_block_title(),
			'attributes' => $block->get_attributes(),
		];
	}

}
