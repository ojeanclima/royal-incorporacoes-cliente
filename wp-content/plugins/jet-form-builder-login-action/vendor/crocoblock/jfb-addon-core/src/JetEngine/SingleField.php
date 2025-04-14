<?php


namespace JetLoginCore\JetEngine;


use JetLoginCore\VueComponentProps;

abstract class SingleField {

	use SmartBaseField;
	use VueComponentProps;

	/**
	 * @return string
	 */
	abstract public function get_name();

	/**
	 * @return string
	 */
	abstract public function get_title();

	public function vue_component_props() {
		return array(
			':all-settings' => 'currentItem.settings'
		);
	}

	/**
	 * Displays a template
	 *
	 * @return void
	 */
	public function render_field_edit() {
		?>
        <template v-if="'<?= $this->get_name(); ?>' === currentItem.settings.type">
            <keep-alive>
                <jet-engine-field-<?= $this->get_name(); ?>
                        v-model="currentItem.settings.<?= $this->get_name(); ?>" <?= $this->vue_component_props_string(); ?>>
            </keep-alive>
        </template>
		<?php
	}

}