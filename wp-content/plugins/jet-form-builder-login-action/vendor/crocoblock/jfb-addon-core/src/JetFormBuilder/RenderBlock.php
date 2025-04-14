<?php


namespace JetLoginCore\JetFormBuilder;


use Jet_Form_Builder\Blocks\Types\Base;
use JetLoginCore\BaseRenderField;

/**
 * @property Base $block_type
 *
 * Trait RenderBlock
 * @package JetLoginCore\JetFormBuilder
 */
trait RenderBlock {

	use BaseRenderField;

	public function set_up() {
		$this->args = $this->block_type->block_attrs;

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

	public function complete_render() {
		return $this->render( null, $this->get_rendered() );
	}

	public function include_field_desc( $custom_desc = false ) {
		$base_desc = $this->block_type->block_attrs['desc'] ?? '';

		if ( false !== $custom_desc ) {
			$this->block_type->block_attrs['desc'] = $custom_desc;
		}
		$response = $this->get_field_desc();

		$this->block_type->block_attrs['desc'] = $base_desc;

		return $response;
	}

	public function include_field_label( $custom_label = false, $custom_mark = false ) {
		$base_label = $this->block_type->block_attrs['label'] ?? '';
		$base_mark  = $this->live_form->spec_data->required_mark ?? '';

		$this->live_form->spec_data->required_mark = ( false === $custom_mark ) ? $base_mark : $custom_mark;

		if ( false !== $custom_label ) {
			$this->block_type->block_attrs['label'] = $custom_label;
		}

		$response = $this->get_field_label();

		$this->block_type->block_attrs['label']    = $base_label;
		$this->live_form->spec_data->required_mark = $base_mark;

		return $response;
	}

	public function include_layout_column() {
		return $this->block_type->get_common_template( 'field-column.php' );
	}

	public function include_layout_row() {
		return $this->block_type->get_common_template( 'field-row.php' );
	}

	public function get_layout_arg() {
		return $this->live_form->spec_data->fields_layout ?? 'column';
	}

	public function main_field_class() {
		return 'jet-form-builder__field';
	}

	public function get_field_name( $name = '' ) {
		return $this->block_type->get_field_name(
			$name ? $name : $this->get_arg( 'name', 'field_name' )
		);
	}

	public function get_required_val() {
		return $this->block_type->get_required_val();
	}

	public function get_field_id() {
		return $this->block_type->get_field_id( $this->args );
	}

}