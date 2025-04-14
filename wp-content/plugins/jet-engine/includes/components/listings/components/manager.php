<?php
namespace Jet_Engine\Listings\Components;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define components manager class
 */
class Manager {

	public $editor;
	public $registry;
	public $state;

	private $is_component_hits = [];

	/**
	 * Constructor for the class
	 */
	public function __construct() {

		require_once $this->path( 'editor.php' );
		require_once $this->path( 'registry.php' );
		require_once $this->path( 'state.php' );

		$this->editor   = new Editor();
		$this->registry = new Registry();
		$this->state    = new State();

	}

	/**
	 * Returns component instance by name
	 * 
	 * @param  [type] $component_name [description]
	 * @return [type]                 [description]
	 */
	public function get( $component_name = '', $by = 'name' ) {
		return $this->registry->get( $component_name, $by );
	}

	/**
	 * Check if given ID is ID of component
	 * 
	 * @param  [type]  $id [description]
	 * @return boolean     [description]
	 */
	public function is_component( $id ) {
		
		if ( isset( $this->is_component_hits[ $id ] ) ) {
			return $this->is_component_hits[ $id ];
		}

		$result = ( 'component' === get_post_meta( $id, '_entry_type', true ) ) ? true : false;

		$this->is_component_hits[ $id ] = $result;

		return $result;
	}

	/**
	 * Returns component category data - slug and name
	 * 
	 * @return [type] [description]
	 */
	public function components_category( $return = 'slug' ) {
		$category_data = [
			'slug' => 'jet-engine-components',
			'name' => __( 'JetEngine Components', 'jet-engine' ),
		];

		return isset( $category_data[ $return ] ) ? $category_data[ $return ] : $category_data;

	}

	/**
	 * Base component name to use in elements and docuemnts
	 * 
	 * @return [type] [description]
	 */
	public function get_component_base_name() {
		return 'jet-engine-component';
	}

	/**
	 * Get supported control types for component properties.
	 * Used to implement UI for various builders.
	 * 
	 * @return [type] [description]
	 */
	public function get_supported_control_types() {
		return [
			'text'          => __( 'Text', 'jet-engine' ),
			'textarea'      => __( 'Textarea', 'jet-engine' ),
			'rich_text'     => __( 'Rich Text', 'jet-engine' ),
			'select'        => __( 'Select', 'jet-engine' ),
			'media'         => __( 'Single Media', 'jet-engine' ),
			//'media_gallery' => __( 'Media Gallery', 'jet-engine' ), temporary disabled until feature became stable
			//'icon'          => __( 'Icon', 'jet-engine' ), temporary disabled until Elementor adds support for the dynamic icons
		];
	}

	/**
	 * Path inside components dir
	 * 
	 * @return [type] [description]
	 */
	public function path( $path = '' ) {
		return jet_engine()->plugin_path( 'includes/components/listings/components/' . $path );
	}

	/**
	 * Path inside components dir
	 * 
	 * @return [type] [description]
	 */
	public function url( $path = '' ) {
		return jet_engine()->plugin_url( 'includes/components/listings/components/' . $path );
	}

	public function set_state( $state ) {
		$this->state = $state;
	}
}
