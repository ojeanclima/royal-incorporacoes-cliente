<?php


namespace JetLoginCore;


trait BaseRenderField {

	public $args;

	public function set_up() {
		return $this;
	}

	abstract public function render_field( $attrs_string );

	/**
	 * It used in JetFormBuilder Render\Base for naming field template
	 * And in JetEngine & JetFormBuilder it used for naming wp.hook
	 *
	 * @return string
	 */
	abstract public function get_name();

	/**
	 * @param string $name
	 */
	abstract public function get_field_name( $name = '' );

	abstract public function get_required_val();

	abstract public function get_field_id();

	/**
	 * Base function, it must rewrite on core-level
	 */
	abstract public function main_field_class();

	/**
	 * Base function, it must rewrite on core-level
	 *
	 * @param $custom_label
	 * @param $custom_mark
	 */
	abstract public function include_field_label( $custom_label = false, $custom_mark = false );

	/**
	 * Base function, it must rewrite on core-level
	 *
	 * @param $custom_desc
	 */
	abstract public function include_field_desc( $custom_desc = false );

	/**
	 * Base function, it must rewrite on core-level
	 */
	abstract public function include_layout_column();

	/**
	 * Base function, it must rewrite on core-level
	 */
	abstract public function include_layout_row();

	/**
	 * Base function, it must rewrite on core-level
	 */
	abstract public function get_layout_arg();

	/**
	 * Base function, it must rewrite on core-level
	 *
	 * @param $template
	 *
	 * @param array $additional
	 *
	 * @return false|string
	 */
	final public function include_layout( $template, $additional = array() ) {
		$custom_label = $additional[0] ?? false;
		$custom_desc  = $additional[1] ?? false;
		$custom_mark  = $additional[2] ?? false;

		$label = $this->include_field_label( $custom_label, $custom_mark );
		$desc  = $this->include_field_desc( $custom_desc );

		$layout = $this->get_layout_arg();

		switch ( $layout ) {
			case 'column':
				ob_start();
				include $this->include_layout_column();

				return ob_get_clean();
			case 'row':
				ob_start();
				include $this->include_layout_row();

				return ob_get_clean();
			default:
				ob_start();
				include $this->include_layout_custom();

				return ob_get_clean();
		}
	}

	/**
	 * It must be rewrite on client-level
	 */
	public function attributes_values() {
		return array();
	}

	/**
	 * @return string
	 */
	public function include_layout_custom() {
		return $this->include_layout_column();
	}

	/**
	 * Base function, it must rewrite on core-level
	 */
	public function _preset_attributes_map() {
		return array();
	}

	final public function get_arg( $key, $if_not_exist = '' ) {
		return ( isset( $this->args[ $key ] ) ? $this->args[ $key ] : $if_not_exist );
	}

	private function save_attributes() {
		$attributes = apply_filters( "jet-fb/attributes/{$this->get_name()}", $this->get_attributes_map() );

		foreach ( $attributes as $name => $value ) {
			$this->add_attribute( $name, $value );
		}
	}

	final public function get_attributes_map() {
		return array_merge_recursive( $this->_preset_attributes_map(), $this->attributes_values() );
	}

	public function get_args( $args_names = array() ) {
		if ( ! $args_names ) {
			return $this->args;
		}
		$response = array();

		foreach ( $args_names as $args_name ) {
			if ( ! is_array( $args_name ) ) {
				$response[ $args_name ] = $this->get_arg( $args_name );

				continue;
			}
			list( $name, $if_not_exist ) = $args_name;

			$response[ $name ] = $this->get_arg( $name, $if_not_exist );
		}

		return $response;
	}

	/**
	 * Call this function to get rendered field template
	 *
	 * @return string
	 */
	final public function get_rendered() {
		$this->save_attributes();

		return $this->render_field( $this->get_attributes_string() );
	}

	public function complete_render() {
		return $this->get_rendered();
	}

}