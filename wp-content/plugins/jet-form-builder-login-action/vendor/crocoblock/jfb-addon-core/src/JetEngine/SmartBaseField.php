<?php


namespace JetLoginCore\JetEngine;


use JetLoginCore\SmartBaseFormField;

trait SmartBaseField {

	public $block_attrs = array();
	public $block_builder = null;

	use SmartBaseFormField;

	public function set_block_data( $attributes ) {
		$this->block_attrs = $attributes[ $this->get_name() ] ?? array();

		$this->block_attrs['type']       = $this->get_name();
		$this->block_attrs['default']    = $attributes['default'] ?? '';
		$this->block_attrs['label']      = $attributes['label'] ?? '';
		$this->block_attrs['desc']       = $attributes['desc'] ?? '';
		$this->block_attrs['class_name'] = $attributes['class_name'] ?? '';
		$this->block_attrs['name']       = $attributes['name'] ?? '';
		$this->block_attrs['required']   = $attributes['required'] ?? false;

		return $this;
	}

	/**
	 * @param $template
	 * @param $args
	 * @param $builder
	 *
	 * @return string
	 */
	public function get_field_template( $template, $args, $builder ) {
		$this->set_block_data( $args );
		$this->block_builder = $builder;

		return $this->get_template();
	}
}