<?php
/**
 * Bricks views manager
 */
namespace Jet_Engine\Modules\Calendar\Bricks_Views;

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Manager {
	/**
	 * Elementor Frontend instance
	 *
	 * @var null
	 */
	public $frontend = null;

	/**
	 * Constructor for the class
	 */
	function __construct() {
		add_action( 'jet-engine/bricks-views/init', array( $this, 'init' ), 10 );
		add_filter( 'jet-engine/calendar/render/default-settings', array( $this, 'add_default_settings' ), 10, 2 );
		add_filter( 'jet-engine/calendar/render/widget-settings', array( $this, 'add_widget_settings' ), 10, 2 );
	}

	public function init() {
		add_action( 'jet-engine/bricks-views/register-elements', array( $this, 'register_elements' ), 11 );
	}

	public function register_elements() {
		\Bricks\Elements::register_element( $this->module_path( 'calendar.php' ) );
	}

	public function module_path( $relative_path = '' ) {
		return jet_engine()->plugin_path( 'includes/modules/calendar/bricks-views/' . $relative_path );
	}

	public function add_default_settings( $default_settings ) {
		$default_settings['_id'] = '';
		return $default_settings;
	}

	public function add_widget_settings( $widget_settings, $settings ) {
		$widget_settings['_id'] = isset( $settings['_id'] ) ? $settings['_id'] : '';
		return $widget_settings;
	}
}