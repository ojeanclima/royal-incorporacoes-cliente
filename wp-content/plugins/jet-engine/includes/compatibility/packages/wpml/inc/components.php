<?php
namespace Jet_Engine\Compatibility\Packages\Jet_Engine_WPML_Package\Components;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Manager {

	/**
	 * A reference to an instance of this class.
	 *
	 * @access private
	 * @var    object
	 */
	private static $instance = null;

	private function __construct() {
		add_filter( 'jet-engine/component/render-id', [ $this, 'set_translation_id_for_render' ] );
		add_action( 'jet-engine/component/init', [ $this, 'hook_elementor_translations' ] );
	}

	/**
	 * Allow to translate components properties as for usual Elementor widget.
	 */
	public function hook_elementor_translations( $component ) {

		add_filter( 'wpml_elementor_widgets_to_translate', function( $nodes_to_translate ) use ( $component ) {

			$component_nodes = [];

			foreach ( $component->get_props() as $prop ) {

				switch ( $prop['control_type'] ) {
					case 'text':
					case 'select':
						$editor_type = 'LINE';
						break;

					case 'textarea':
					case 'rich_text':
						$editor_type = 'AREA';
						break;

					default:
						$editor_type = false;
						break;
				}

				if ( $editor_type ) {
					$component_nodes[] = [
						'field'       => $prop['control_name'],
						'type'        => $component->get_display_name() . ': ' . $prop['control_label'],
						'editor_type' => $editor_type,
					];
				}
			}

			if ( ! empty( $component_nodes ) ) {
				$nodes_to_translate[ $component->get_element_name() ] = [
					'conditions' => [ 'widgetType' => $component->get_element_name() ],
					'fields'     => $component_nodes,
				];
			}

			return $nodes_to_translate;
		} );
	}

	/**
	 * Set translated component ID to render
	 *
	 * @param  int $id Initial ID.
	 * @return int
	 */
	public function set_translation_id_for_render( $id ) {
		return apply_filters( 'wpml_object_id', $id, jet_engine()->listings->post_type->slug(), true );
	}

	/**
	 * Returns the instance.
	 *
	 * @access public
	 * @return object
	 */
	public static function instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
