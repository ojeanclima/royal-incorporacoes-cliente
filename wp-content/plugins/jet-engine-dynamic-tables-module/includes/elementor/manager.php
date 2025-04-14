<?php
namespace Jet_Engine_Dynamic_Tables\Elementor;

class Manager {

	public function __construct() {

		if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '3.5.0', '>=' ) ) {
			add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ), 10 );
		} else {
			add_action( 'elementor/widgets/widgets_registered', array( $this, 'register_widgets' ), 10 );
		}
	}

	public function register_widgets( $widgets_manager ) {

		if ( method_exists( $widgets_manager, 'register' ) ) {
			$widgets_manager->register( new Table_Widget() );
		} else {
			$widgets_manager->register_widget_type( new Table_Widget() );
		}
	}

}
