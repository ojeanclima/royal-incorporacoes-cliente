<?php


namespace Jet_FB_Login\JetFormBuilder\Blocks;

use Jet_FB_Login\JetFormBuilder\Blocks\Button\ResetPasswordButton;
use Jet_FB_Login\JetFormBuilder\Blocks\Field\ResetPasswordField;
use JetLoginCore\JetFormBuilder\BlocksManager;

class Manager extends BlocksManager {

	public function fields() {
		return array(
			new ResetPasswordButton(),
			new ResetPasswordField(),
		);
	}

	/**
	 * Supported only >= 3.4.0 JetFormBuilder
	 *
	 * @return bool
	 */
	public function can_init(): bool {
		return class_exists( '\JFB_Modules\Actions_V2\Module' );
	}

	public function on_base_need_install() {
	}

	public function on_base_need_update() {
	}


}
