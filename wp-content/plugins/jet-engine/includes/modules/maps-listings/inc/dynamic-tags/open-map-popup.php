<?php
namespace Jet_Engine\Modules\Maps_Listings\Dynamic_Tags;

use Jet_Engine\Modules\Maps_Listings\Module;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Open_Map_Popup extends \Elementor\Core\DynamicTags\Tag {

	public function get_name() {
		return 'jet-open-map-listing-popup';
	}

	public function get_title() {
		return __( 'Open Map Listing Popup', 'jet-engine' );
	}

	public function get_group() {
		return \Jet_Engine_Dynamic_Tags_Module::JET_ACTION_GROUP;
	}

	public function get_categories() {
		return array(
			\Jet_Engine_Dynamic_Tags_Module::URL_CATEGORY,
		);
	}

	public function is_settings_required() {
		return true;
	}

	protected function register_controls() {

		$this->add_control(
			'specific_post_id',
			array(
				'label'       => __( 'Specific post ID (optional)', 'jet-engine' ),
				'label_block' => true,
				'type'        => \Elementor\Controls_Manager::TEXT,
				'ai'          => array(
					'active' => false,
				),
			)
		);

		$this->add_control(
			'event',
			array(
				'label'   => __( 'Trigger', 'jet-engine' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'click',
				'options' => array(
					'click' => __( 'On Click', 'jet-engine' ),
					'hover' => __( 'On Hover', 'jet-engine' ),
				)

			)
		);

		$this->add_control(
			'zoom',
			array(
				'label' => __( 'Zoom', 'jet-engine' ),
				'type'  => \Elementor\Controls_Manager::NUMBER,
			)
		);

		$this->add_control(
			'scroll_to_map',
			array(
				'label' => __( 'Scroll to Map', 'jet-engine' ),
				'type'  => \Elementor\Controls_Manager::SWITCHER,
			)
		);
	}

	public function render() {
		$specific_post_id = $this->get_settings( 'specific_post_id' );
		$event = $this->get_settings( 'event' );
		$zoom  = $this->get_settings( 'zoom' );

		$params = array();

		if ( ! empty( $zoom ) ) {
			$params['zoom'] = $zoom;
		}

		$scroll_to_map = $this->get_settings( 'scroll_to_map' );
		$scroll_to_map = filter_var( $scroll_to_map, FILTER_VALIDATE_BOOLEAN );

		if ( $scroll_to_map ) {
			$params['scroll_to_map'] = 'yes';
		}

		echo Module::instance()->get_action_url( $specific_post_id, $event, $params );
	}

}
