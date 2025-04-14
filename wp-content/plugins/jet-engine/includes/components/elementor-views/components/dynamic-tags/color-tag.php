<?php
namespace Jet_Engine\Elementor_Views\Components\Dynamic_Tags;

use \Jet_Engine_Dynamic_Tags_Module;
use \Jet_Engine\Listings\Components\Component;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Color_Tag extends \Elementor\Core\DynamicTags\Tag {

	public function get_name() {
		return 'jet-component-color-tag';
	}

	public function get_title() {
		return __( 'Component Control Color', 'jet-engine' );
	}

	public function get_group() {
		return Jet_Engine_Dynamic_Tags_Module::JET_GROUP;
	}

	public function get_categories() {
		return [
			Jet_Engine_Dynamic_Tags_Module::COLOR_CATEGORY
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

	public function render() {

		$control_name = $this->get_settings( 'control_name' );

		if ( empty( $control_name ) ) {
			return;
		}

		printf( 'var(%1$s%2$s)', Component::css_var_prefix(), $control_name );

	}
}
