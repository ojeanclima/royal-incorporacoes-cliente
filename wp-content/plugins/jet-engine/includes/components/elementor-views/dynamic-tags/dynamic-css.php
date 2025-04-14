<?php
use Elementor\Element_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Jet_Engine_Elementor_Dynamic_CSS extends Elementor\Core\DynamicTags\Dynamic_CSS {

	private $post_id;
	private $_post_id_for_data;
	protected $listing_selector;

	public function __construct( $post_id, $post_id_for_data ) {

		$this->post_id = $post_id;
		$this->_post_id_for_data = $post_id_for_data;

		$post_css_file = Elementor\Core\Files\CSS\Post::create( $post_id_for_data );

		parent::__construct( $post_id, $post_css_file );

	}

	/**
	 * Set unique selector for JetEngine listings
	 *
	 * @param string $selector [description]
	 */
	public function set_listing_unique_selector( $selector = '' ) {
		$this->listing_selector = $selector;
	}

	/**
	 * Returns
	 * @return [type] [description]
	 */
	public function get_listing_unique_selector() {
		return apply_filters( 'jet-engine/elementor-views/dynamic-css/unique-listing-selector', $this->listing_selector );
	}

	/**
	 * Get unique element selector.
	 *
	 * Retrieve the unique selector for any given element.
	 *
	 * @since 1.2.0
	 * @access public
	 *
	 * @param Element_Base $element The element.
	 *
	 * @return string Unique element selector.
	 */
	public function get_element_unique_selector( Element_Base $element ) {

		$listing_selector = $this->get_listing_unique_selector();
		$base_selector    = $listing_selector ? $listing_selector : '.elementor-' . $this->post_id;

		return $base_selector . ' .elementor-element' . $element->get_unique_selector();
	}

	public function get_post_id_for_data() {
		return $this->_post_id_for_data;
	}

}
