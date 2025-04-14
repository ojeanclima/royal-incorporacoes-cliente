<?php


namespace Jet_FB_Login\JetFormBuilder\Blocks\Button;

use Jet_FB_Login\JetFormBuilder\Blocks\ResetPasswordFieldTools;
use Jet_FB_Login\JetFormBuilder\RenderStates\OnResetPasswordState;
use Jet_Form_Builder\Blocks\Conditional_Block\Render_State;
use Jet_Form_Builder\Blocks\Types\Action_Button;
use Jet_Form_Builder\Blocks\Types\Base;

class ResetPasswordButton extends Base {

	public $use_style_manager = false;

	public function get_name() {
		return 'reset-password-button';
	}

	public function use_preset() {
		return false;
	}

	public function render_row_layout() {
		return false;
	}

	public function get_block_renderer( $wp_block = null ) {
		$button = jet_form_builder()->blocks->get_field_by_name( Action_Button::class );

		if ( ! $button ) {
			return '';
		}

		$collection = Render_State::instance()->get_current();

		$attributes = array_merge(
			array(
				'label' => $collection->in_array( OnResetPasswordState::class )
					? $this->block_attrs['reset_label'] ?? ''
					: $this->block_attrs['default_label'] ?? '',
			),
			$this->block_attrs
		);

		return $button->render_callback_field( $attributes );
	}

	public function get_path_metadata_block() {
		return ResetPasswordFieldTools::get_blocks_dir( 'Button' );
	}

}
