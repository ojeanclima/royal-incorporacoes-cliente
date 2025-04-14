<?php


namespace Jet_FB_Login\JetFormBuilder\Blocks\Field;

use Jet_Form_Builder\Blocks\Types\Base;
use Jet_FB_Login\JetFormBuilder\Blocks\ResetPasswordFieldTools;
use Jet_FB_Login\JetFormBuilder\RenderStates\OnResetPasswordState;
use Jet_Form_Builder\Blocks\Conditional_Block\Render_State;
use Jet_Form_Builder\Blocks\Types\Text_Field;

class ResetPasswordField extends Base {

	public $use_style_manager = false;

	public function get_name() {
		return 'reset-password';
	}

	public function use_preset() {
		return false;
	}

	public function render_row_layout() {
		return false;
	}

	public function get_block_renderer( $wp_block = null ) {
		$user_field = jet_form_builder()->blocks->get_field_by_name( Text_Field::class );

		if ( ! $user_field ) {
			return '';
		}

		$password = clone $user_field;

		$password->attrs['field_type'] = array(
			'default' => 'password',
		);

		$confirm_password = clone $password;

		$collection = Render_State::instance()->get_current();

		return $collection->in_array( OnResetPasswordState::class )
			? $password->render_callback_field(
				ResetPasswordFieldTools::get_attrs_by_pref( 'password_', $this->block_attrs )
			) .
				$confirm_password->render_callback_field(
					ResetPasswordFieldTools::get_attrs_by_pref( 'confirm_password_', $this->block_attrs )
				)
			: $user_field->render_callback_field(
				ResetPasswordFieldTools::get_attrs_by_pref( 'user_', $this->block_attrs )
			);
	}

	public function get_path_metadata_block() {
		return ResetPasswordFieldTools::get_blocks_dir( 'Field' );
	}

}
