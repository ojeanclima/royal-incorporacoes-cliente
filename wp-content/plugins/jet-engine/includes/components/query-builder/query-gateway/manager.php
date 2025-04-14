<?php
namespace Jet_Engine\Query_Builder\Query_Gateway;

use Jet_Engine\Query_Builder\Manager as Query_Builder_Manager;

/**
 * Gateway to connect Query Builder to any Elementor widget containing Repeater element to showcase content
 */
class Manager {

	private $_controls_map = array();

	//$depth and $object_stack added to fix https://github.com/Crocoblock/issues-tracker/issues/10536
	private $depth = 0;
	private $initial_object = null;

	private $object_stack = array();
	private $processing_item = null;

	private $new_hooks = true;

	public function __construct() {

		add_action( 'jet-engine-query-gateway/control', array( $this, 'register_controls' ), 10, 2 );
		add_action( 'jet-engine-query-gateway/do-item', array( $this, 'set_item_object' ) );
		add_action( 'jet-engine-query-gateway/reset-item', array( $this, 'reset_item_object' ) );
		add_filter( 'jet-engine-query-gateway/query', array( $this, 'query_items' ), 10, 3 );

		//remove version checks and old methods after a few updates of JetElements/JetTabs
		if ( function_exists( 'jet_elements' ) && version_compare( jet_elements()->get_version(), '2.7.2', '<=' ) ) {
			$this->new_hooks = false;
		}
		
		if ( $this->new_hooks && function_exists( 'jet_tabs' ) && version_compare( jet_tabs()->get_version(), '2.2.6.1', '<=' ) ) {
			$this->new_hooks = false;
		}
		
		if ( $this->new_hooks ) {
			add_action( 'jet-engine-query-gateway/before-loop', array( $this, 'before_loop' ) );
			add_action( 'jet-engine-query-gateway/reset-item', array( $this, 'after_loop' ), 11 );
		}

		// Native Jet-plugins compatibility
		foreach ( array( 'jet-tabs', 'jet-elements' ) as $plugin_slug ) {
			add_filter( $plugin_slug . '/widget/loop-items', array( $this, 'jet_plugins_compatibility' ), 10, 3 );
		}

	}

	public function before_loop() {
		$current_object = jet_engine()->listings->data->get_current_object();
		$this->add_to_stack( $current_object );
	}

	public function after_loop() {
		jet_engine()->listings->data->set_current_object( $this->get_from_stack() );
		$this->remove_from_stack();
	}

	public function increase_stack_depth() {
		$this->depth++;
	}

	public function decrease_stack_depth() {
		$this->depth--;
	}

	public function get_from_stack() {
		if ( ! $this->new_hooks ) {
			return $this->object_stack[ $this->depth ] ?? false;
		}

		$i = array_key_last( $this->object_stack );
		return ! empty( $this->object_stack ) ? $this->object_stack[ $i ] : false;
	}

	public function add_to_stack( $object ) {
		if ( ! $this->new_hooks ) {
			$this->object_stack[ $this->depth ] = $object;
			return;
		}

		$this->object_stack[] = $object;
	}

	public function remove_from_stack() {
		if ( ! $this->new_hooks ) {
			unset( $this->object_stack[ $this->depth ] );
			return;
		}

		array_pop( $this->object_stack );
	}
	
	public function set_item_object( $item ) {
		if ( ! $this->new_hooks ) {
			$this->old_set_item_object( $item );
			return;
		}
		
		if ( ! empty( $item['_jet_engine_queried_object'] ) ) {

			// Store initial object
			if ( ! $this->initial_object ) {
				$this->initial_object = jet_engine()->listings->data->get_current_object();
			} else {
				// Remove prevent item from stack.
				$prev_item = jet_engine()->listings->data->get_current_object();

				if ( $prev_item !== $this->initial_object ) {
					do_action( 'jet-engine/object-stack/decrease', $prev_item );
				}
			}

			$this->processing_item = $item['_jet_engine_queried_object'];

			jet_engine()->listings->data->set_current_object( $item['_jet_engine_queried_object'] );

			// Add item to stack.
			do_action( 'jet-engine/object-stack/increase', $item['_jet_engine_queried_object'] );
			add_action( 'jet-engine/listings/frontend/object-done', array( $this, 'add_processing_item_to_stack' ), 20 );
		}
	}

	public function old_set_item_object( $item ) {
		
		if ( ! empty( $item['_jet_engine_queried_object'] ) ) {

			// Add object to stack if it is not set previously for the current depth
			if ( ! $this->get_from_stack() ) {
				$this->add_to_stack( jet_engine()->listings->data->get_current_object() );
			} else {
				// Remove previous item from JetEngine stack.
				$prev_item = jet_engine()->listings->data->get_current_object();

				if ( $prev_item !== $this->get_from_stack() ) {
					do_action( 'jet-engine/object-stack/decrease', $prev_item );
				}
			}

			$this->processing_item = $item['_jet_engine_queried_object'];

			jet_engine()->listings->data->set_current_object( $item['_jet_engine_queried_object'] );

			// Add item to stack.
			do_action( 'jet-engine/object-stack/increase', $item['_jet_engine_queried_object'] );
			add_action( 'jet-engine/listings/frontend/object-done', array( $this, 'add_processing_item_to_stack' ), 20 );
		}
	}

	public function add_processing_item_to_stack() {
		if ( $this->processing_item ) {
			do_action( 'jet-engine/object-stack/increase', $this->processing_item );
		}
	}

	public function reset_item_object() {

		if ( ! $this->new_hooks ) {
			$this->old_reset_item_object();
			return;
		}

		$this->processing_item = null;
		remove_action( 'jet-engine/listings/frontend/object-done', array( $this, 'add_processing_item_to_stack' ), 20 );

		if ( ! $this->initial_object ) {
			return;
		}

		if ( $this->initial_object === jet_engine()->listings->data->get_current_object() ) {
			$this->initial_object = null;
			return;
		}

		// Remove last item from stack.
		$last_item = jet_engine()->listings->data->get_current_object();
		do_action( 'jet-engine/object-stack/decrease', $last_item );

		jet_engine()->listings->data->set_current_object( $this->initial_object );

		$this->initial_object = null;
	}

	public function old_reset_item_object() {
		
		$this->processing_item = null;

		remove_action( 'jet-engine/listings/frontend/object-done', array( $this, 'add_processing_item_to_stack' ), 20 );

		if ( ! $this->get_from_stack() ) {
			return;
		}

		if ( $this->get_from_stack() === jet_engine()->listings->data->get_current_object() ) {
			$this->remove_from_stack();
			return;
		}

		// Remove last item from stack.
		$last_item = jet_engine()->listings->data->get_current_object();
		do_action( 'jet-engine/object-stack/decrease', $last_item );

		//Set JetEngine current object to object from the stack
		jet_engine()->listings->data->set_current_object( $this->get_from_stack() );

		$this->remove_from_stack();
	}

	public function query_items( $items, $control_name, $widget ) {

		if ( $this->is_control_supported( $widget->get_name(), $control_name ) && $this->query_enbaled( $control_name, $widget ) ) {
			$items = $this->get_queried_items( $control_name, $widget );
		}

		return $items;
	}

	public function query_enbaled( $control_name, $widget ) {

		$is_active   = $widget->get_settings( 'jet_engine_query_' . $control_name );
		$query_id    = $widget->get_settings( 'jet_engine_query_id_' . $control_name );
		$control_val = $widget->get_settings( $control_name );

		if ( ! $is_active || ! $query_id || empty( $control_val ) ) {
			return false;
		} else {
			return true;
		}

	}

	public function get_queried_items( $control_name, $widget ) {

		$query_id = $widget->get_settings( 'jet_engine_query_id_' . $control_name );

		$items = array();

		$query = Query_Builder_Manager::instance()->get_query_by_id( $query_id );

		if ( ! $query ) {
			return $items;
		}

		$query->setup_query();

		$query_items = $query->get_items();

		if ( empty( $query_items ) ) {
			return $items;
		}

		$fields_map = $widget->get_settings( $control_name );

		if ( empty( $fields_map ) ) {
			return $items;
		}

		$fields_map = $fields_map[0];

		$this->initial_object = jet_engine()->listings->data->get_current_object();

		foreach ( $query_items as $index => $item ) {
			jet_engine()->listings->data->set_current_object( $item );
			$control = $widget->get_controls( $control_name );
			$parsed_item = $widget->parse_dynamic_settings( $fields_map, $control['fields'], $fields_map );
			$parsed_item['_jet_engine_queried_object'] = $item;
			$parsed_item['_id'] = ! empty( $parsed_item['_id'] ) ? $parsed_item['_id'] . '-' . $index : $control_name . '-' . $index;
			$items[] = $parsed_item;
		}

		$this->reset_item_object();

		return $items;
	}

	public function is_control_supported( $widget_name, $control_name ) {
		if ( empty( $this->_controls_map[ $widget_name ] ) ) {
			return false;
		}

		return in_array( $control_name, $this->_controls_map[ $widget_name ] );
	}

	public function jet_plugins_compatibility( $items, $control_name, $widget ) {
		if ( ! $this->new_hooks ) {
			return $this->old_jet_plugins_compatibility( $items, $control_name, $widget );
		}

		return apply_filters( 'jet-engine-query-gateway/query', $items, $control_name, $widget );
	}

	public function old_jet_plugins_compatibility( $items, $control_name, $widget ) {
		$current_object = jet_engine()->listings->data->get_current_object();
		
		$items = apply_filters( 'jet-engine-query-gateway/query', $items, $control_name, $widget );
		
		if ( ! empty( $items ) && $this->query_enbaled( $control_name, $widget ) ) {
			$this->add_to_stack( $current_object );
			$this->increase_stack_depth();

			//add action to decrease stack depth only if some items in widget loop
			//this is because a widget may have condition to stop rendering if no items present
			//also, add action to reset object
			add_action( 'jet-engine-query-gateway/reset-item', array( $this, 'decrease_stack_depth' ), 0 );
			add_action( 'jet-engine-query-gateway/reset-item', array( $this, 'reset_item_object' ) );
		} else {
			//if items empty we should ensure that stack depth is unchanged, otherwise stack depth will be wrong
			//also, no reset is needed in that case
			remove_action( 'jet-engine-query-gateway/reset-item', array( $this, 'decrease_stack_depth' ), 0 );
			remove_action( 'jet-engine-query-gateway/reset-item', array( $this, 'reset_item_object' ) );
		}
		
		return $items;
	}

	public function store_widget_data( $widget_name, $control_name ) {

		if ( empty( $this->_controls_map[ $widget_name ] ) ) {
			$this->_controls_map[ $widget_name ] = array();
		}

		$this->_controls_map[ $widget_name ][] = $control_name;

	}

	public function register_controls( $widget, $control_name ) {

		$this->store_widget_data( $widget->get_name(), $control_name );

		$widget->add_control(
			'jet_engine_query_' . $control_name,
			array(
				'label'       => esc_html__( 'Use JetEngine query', 'jet-engine' ),
				'description' => esc_html__( 'Select JetEngine query from Query Builder as source of content for this widget.', 'jet-engine' ),
				'type'        => \Elementor\Controls_Manager::SWITCHER,
				'default'     => '',
				'separator'   => 'before',
			)
		);

		$widget->add_control(
			'jet_engine_query_id_' . $control_name,
			array(
				'label'   => __( 'Select Query', 'jet-engine' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => Query_Builder_Manager::instance()->get_queries_for_options(),
				'condition' => array(
					'jet_engine_query_' . $control_name => 'yes',
				),
			)
		);

		$widget->add_control(
			'jet_engine_query_instructions_' . $control_name,
			array(
				'type'      => \Elementor\Controls_Manager::RAW_HTML,
				'raw'       => $this->get_instructions_message(),
				'condition' => array(
					'jet_engine_query_' . $control_name => 'yes',
				),
			)
		);

	}

	public function get_instructions_message() {

		$message = '<b>' . __( 'Instructions', 'jet-engine' ) . '</b><br><br>';
		$message .= '<ul>';
		$message .= '<li style="padding: 0 0 8px;"><i>1. ' . __( 'Select query to use as content source', 'jet-engine' ) . '</i></li>';
		$message .= '<li style="padding: 0 0 8px;"><i>2. ' . __( 'Add <b>one</b> static item with Repeater control below to set dynamic data map', 'jet-engine' ) . '</i></li>';
		$message .= '<li><i>3. ' . __( 'Use <b>JetEngine Dynamic Tags</b> to map appropriate item fields to required dynamic data', 'jet-engine' ) . '</i></li>';
		$message .= '</ul>';

		return $message;

	}

}
