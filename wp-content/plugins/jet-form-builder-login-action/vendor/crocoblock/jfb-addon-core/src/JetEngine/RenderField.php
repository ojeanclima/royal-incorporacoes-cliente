<?php


namespace JetLoginCore\JetEngine;


use Jet_Engine_Booking_Forms_Builder;
use JetLoginCore\AttributesTrait;
use JetLoginCore\BaseRenderField;

trait RenderField {

	use AttributesTrait;
	use BaseRenderField;

	/** @var Jet_Engine_Booking_Forms_Builder */
	private $builder;

	public function __construct( $block_type ) {
		$this->args    = $block_type->block_attrs;
		$this->builder = $block_type->block_builder;

		return $this;
	}

	public function _preset_attributes_map() {
		return array(
			'class'           => array( $this->main_field_class() ),
			'required'        => $this->get_required_val(),
			'name'            => $this->get_field_name(),
			'id'              => $this->get_field_id(),
			'data-field-name' => $this->get_arg( 'name', 'field_name' ),
			'value'           => $this->get_arg( 'default' ),
		);
	}

	public function include_field_desc( $custom_desc = false ) {
		$args = array_merge( $this->args, array(
			'desc' => false !== $custom_desc ? $custom_desc : $this->get_arg( 'desc' )
		) );

		return $this->builder->get_field_desc( $args );
	}

	public function include_field_label( $custom_label = false, $custom_mark = false ) {
		$args      = array_merge( $this->args, array(
			'label' => false !== $custom_label ? $custom_label : $this->get_arg( 'label' )
		) );
		$base_mark = $this->builder->args['required_mark'] ?? '';

		$this->builder->args['required_mark'] = ( false === $custom_mark ) ? $base_mark : $custom_mark;

		$response = $this->builder->get_field_label( $args );

		$this->builder->args['required_mark'] = $base_mark;

		return $response;
	}

	public function include_layout_column() {
		return jet_engine()->get_template( 'forms/common/field-column.php' );
	}

	public function include_layout_row() {
		return jet_engine()->get_template( 'forms/common/field-row.php' );
	}


	public function get_layout_arg() {
		return $this->builder->args['fields_layout'] ?? 'column';
	}

	public function main_field_class() {
		return 'jet-form__field';
	}

	public function get_field_name( $name = '' ) {
		return $this->builder->get_field_name(
			$name ? $name : $this->get_arg( 'name', 'field_name' )
		);
	}

	public function get_required_val() {
		return $this->builder->get_required_val( $this->args );
	}

	public function get_field_id() {
		return $this->builder->get_field_id( $this->args );
	}

}