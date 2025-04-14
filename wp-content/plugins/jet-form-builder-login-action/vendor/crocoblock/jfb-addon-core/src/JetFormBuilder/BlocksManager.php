<?php


namespace JetLoginCore\JetFormBuilder;


use Jet_Form_Builder\Admin\Editor;
use JetLoginCore\BaseFormFieldsManager;
use JetLoginCore\FormFieldLocalize;

class BlocksManager extends BaseFormFieldsManager {

	use EditorAssetsManager;
	use WithInit;
	use FormFieldLocalize;

	public function plugin_version_compare() {
		return '1.2.0';
	}

	public function handler_for_localize() {
		return Editor::EDITOR_PACKAGE_HANDLE;
	}

	public function on_plugin_init() {
		add_action( 'jet-form-builder/blocks/register', function ( $manager ) {
			add_action(
				'jet-form-builder/editor-assets/before',
				array( $this, 'maybe_localize_block_data' )
			);
			$this->assets_init();

			foreach ( $this->get_fields() as $block ) {
				$manager->register_block_type( $block );
			}
		} );
	}

	/**
	 * @return void
	 */
	public function before_init_editor_assets() {
		// TODO: Implement before_init_editor_assets() method.
	}

}