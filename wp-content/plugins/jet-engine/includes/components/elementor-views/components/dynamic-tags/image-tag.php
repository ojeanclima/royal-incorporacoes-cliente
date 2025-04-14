<?php
namespace Jet_Engine\Elementor_Views\Components\Dynamic_Tags;

use Jet_Engine_Dynamic_Tags_Module;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Image_Tag extends \Elementor\Core\DynamicTags\Data_Tag {

	public function get_name() {
		return 'jet-component-tag-image';
	}

	public function get_title() {
		return __( 'Component Control Image', 'jet-engine' );
	}

	public function get_group() {
		return Jet_Engine_Dynamic_Tags_Module::JET_GROUP;
	}

	public function get_categories() {
		return [
			Jet_Engine_Dynamic_Tags_Module::IMAGE_CATEGORY
		];
	}

	public function is_settings_required() {
		return true;
	}

	protected function register_controls() {
		$this->add_control(
			'control_name',
			[
				'label'  => __( 'Control Name', 'jet-engine' ),
				'type'   => \Elementor\Controls_Manager::TEXT,
			]
		);
	}

	public function get_value( array $options = array() ) {

		$control_name = $this->get_settings( 'control_name' );

		if ( empty( $control_name ) ) {
			return [];
		}

		$value = jet_engine()->listings->components->state->get( $control_name );

		return $value;

	}
}
