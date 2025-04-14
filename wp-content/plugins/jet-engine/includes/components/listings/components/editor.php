<?php
namespace Jet_Engine\Listings\Components;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define components editor class
 */
class Editor {

	private $settings_template_hooked = false;

	/**
	 * Constructor for the class
	 */
	public function __construct() {

		add_filter( 'jet-engine/templates/localized-settings', [ $this, 'add_button_template' ] );
		add_filter( 'jet-engine/templates/editor-views-list', [ $this, 'component_view_link' ], 10, 3 );
		add_filter( 'jet-engine/templates/edit-settings-button', [ $this, 'component_settings_button' ], 10, 2 );

		add_action( 'jet-engine/templates/before-listing-assets', [ $this, 'editor_assets' ] );
		add_action( 'wp_ajax_jet_engine_get_component_settings', [ $this, 'ajax_get_settings' ] );
		add_action( 'wp_ajax_jet_engine_set_component_settings', [ $this, 'ajax_set_settings' ] );

	}

	/**
	 * AJAX callback to set component settings
	 * 
	 * @return [type] [description]
	 */
	public function ajax_set_settings() {

		$component_id = ! empty( $_REQUEST['component_id'] ) ? absint( $_REQUEST['component_id'] ) : false;

		if ( ! $component_id || ! current_user_can( 'edit_post', $component_id ) ) {
			wp_send_json_error( __( 'You can`t edit this compnent', 'jet-engine' ) );
		}

		$nonce_action = jet_engine()->listings->post_type->get_nonce_action();

		if ( empty( $_REQUEST['_nonce'] ) || ! wp_verify_nonce( $_REQUEST['_nonce'], $nonce_action ) ) {
			wp_send_json_error( __( 'Page is expired, please reload it and try again', 'jet-engine' ) );
		}

		$settings = ! empty( $_REQUEST['settings'] ) ? $_REQUEST['settings'] : [];
		$component = jet_engine()->listings->components->get( $component_id, 'id' );

		$props  = ! empty( $settings['component_controls_list'] ) ? $settings['component_controls_list'] : [];
		$styles = ! empty( $settings['component_style_controls_list'] ) ? $settings['component_style_controls_list'] : [];

		$component->set_props( $props );
		$component->set_styles( $styles );

		$template_view = get_post_meta( $component_id, '_listing_type', true );

		do_action( 'jet-engine/listings/components/update-settings', $component );

		wp_send_json_success( 
			jet_engine()->listings->post_type->admin_screen->get_edit_url( $template_view, $component_id )
		);

	}

	/**
	 * AJAX callback to get component settings
	 * 
	 * @return [type] [description]
	 */
	public function ajax_get_settings() {

		$component_id = ! empty( $_REQUEST['component_id'] ) ? absint( $_REQUEST['component_id'] ) : false;

		if ( ! $component_id || ! current_user_can( 'edit_post', $component_id ) ) {
			wp_send_json_error( __( 'You can`t edit this compnent', 'jet-engine' ) );
		}

		$nonce_action = jet_engine()->listings->post_type->get_nonce_action();

		if ( empty( $_REQUEST['_nonce'] ) || ! wp_verify_nonce( $_REQUEST['_nonce'], $nonce_action ) ) {
			wp_send_json_error( __( 'Page is expired, please reload it and try again', 'jet-engine' ) );
		}

		$component = jet_engine()->listings->components->get( $component_id, 'id' );

		$settings = [
			'component_controls_list'       => $component->get_props(),
			'component_style_controls_list' => $component->get_styles(),
		];

		wp_send_json_success( $settings );

	}

	/**
	 * Change edit settings button for the component
	 * 
	 * @return [type] [description]
	 */
	public function component_settings_button( $button, $post_id ) {

		if ( jet_engine()->listings->components->is_component( $post_id ) ) {

			$button = sprintf(
				'<button type="button" class="button button-small jet-engine-component-edit-settings" data-component-id="%1$s">%2$s<span class="spinner"></span></button>',
				$post_id,
				__( 'Edit Component Settings', 'jet-engine' )
			);

			if ( false === $this->settings_template_hooked ) {

				add_action( 'admin_footer', [ $this, 'render_settings_template' ] );

				$this->settings_template_hooked = true;
			}

		}

		return $button;
	}

	public function render_settings_template() {
		?>
		<script type="text/x-template" id="jet-engine-component-edit-settings-tmpl">
			<div class="jet-engine-component-settings">
				<div class="jet-engine-component-settings__nav">
					<a
						href="#"
						:class="{ 
							'jet-engine-component-settings__nav-link': true, 
							'is-active': 'content' === componentControlsMode
						}"
						@click.prevent="componentControlsMode = 'content'"
					><?php _e( 'Content Controls', 'jet-engine' ); ?></a>
					|
					<a
						href="#"
						:class="{ 
							'jet-engine-component-settings__nav-link': true, 
							'is-active': 'style' === componentControlsMode
						}"
						@click.prevent="componentControlsMode = 'style'"
					><?php _e( 'Style Controls', 'jet-engine' ); ?></a>
				</div>
				<cx-vui-repeater
					:style="{ marginBottom: '2px' }"
					button-label="<?php _e( 'New Control', 'jet-engine' ); ?>"
					button-style="accent-border"
					button-size="mini"
					v-model="settings.component_controls_list"
					v-if="'content' === componentControlsMode"
					@add-new-item="addNewControl( 'component_controls_list', {
						control_label: '',
						control_name: '',
						control_type: 'text',
						control_options: '',
						control_default: '',
						control_default_image: '',
						control_default_icon: '',
					} )"
				>
					<cx-vui-repeater-item
						v-for="( control, index ) in settings.component_controls_list"
						:title="control.control_label"
						:subtitle="control.control_name"
						:collapsed="isCollapsed( control )"
						:index="index"
						@clone-item="cloneControl( $event, 'component_controls_list' )"
						@delete-item="deleteControl( $event, 'component_controls_list' )"
						:key="control.id ? control.id : control.id = getRandomID()"
						:ref="'control' + control.id"
					>
						<cx-vui-input
							label="<?php _e( 'Control Label', 'jet-engine' ); ?>"
							description="<?php _e( 'Control label to show in the component UI in editor', 'jet-engine' ); ?>"
							:wrapper-css="[ 'vertical-fullwidth' ]"
							size="fullwidth"
							:value="control.control_label"
							@input="setControlProp( 'component_controls_list', 'control_label', $event, index )"
							@on-input-change="preSetControlName( index, 'component_controls_list' )"
						></cx-vui-input>
						<cx-vui-input
							label="<?php _e( 'Control Name', 'jet-engine' ); ?>"
							description="<?php _e( 'Control key/name to save into the DB. Please use only lowercase letters, numbers and `_`. Also please note - name must be unique for this component (for both - styles and controls)', 'jet-engine' ); ?>"
							:wrapper-css="[ 'vertical-fullwidth' ]"
							size="fullwidth"
							:value="control.control_name"
							@input="setControlProp( 'component_controls_list', 'control_name', $event, index )"
						></cx-vui-input>
						<cx-vui-select
							label="<?php _e( 'Control Type', 'jet-engine' ); ?>"
							description="<?php _e( 'Type of control for UI', 'jet-engine' ); ?>"
							:wrapper-css="[ 'vertical-fullwidth' ]"
							size="fullwidth"
							:options-list="componentControlTypes()"
							:value="control.control_type"
							@input="setControlProp( 'component_controls_list', 'control_type', $event, index )"
						></cx-vui-select>
						<cx-vui-textarea
							label="<?php _e( 'Options', 'jet-engine' ); ?>"
							description="<?php _e( 'One option per line. Split label and value with `::`, for example - red::Red', 'jet-engine' ); ?>"
							:wrapper-css="[ 'vertical-fullwidth' ]"
							size="fullwidth"
							:value="control.control_options"
							@input="setControlProp( 'component_controls_list', 'control_options', $event, index )"
							v-if="'select' === control.control_type"
						></cx-vui-textarea>
						<cx-vui-textarea
							label="<?php _e( 'Default Value', 'jet-engine' ); ?>"
							description="<?php _e( 'Default value of the given control', 'jet-engine' ); ?>"
							:wrapper-css="[ 'vertical-fullwidth' ]"
							size="fullwidth"
							:value="control.control_default"
							@input="setControlProp( 'component_controls_list', 'control_default', $event, index )"
							v-if="'media' !== control.control_type"
						></cx-vui-textarea>
						<cx-vui-component-wrapper
							label="<?php _e( 'Default Value', 'jet-engine' ); ?>"
							description="<?php _e( 'Default value of the given control', 'jet-engine' ); ?>"
							:wrapper-css="[ 'media' ]"
							v-if="'media' === control.control_type"
						>
							<div
								class="jet-engine-component-settings__media"
								@click="openMediaFrame( control, index )"
							>
								<div
									class="jet-engine-component-settings__media-preview"
									v-if="hasControlDefaultImage( control )"
								>
									<img
										:src="defaultImageSRC( control.control_default_image )"
										alt=""
									>
									<div
										class="jet-engine-component-settings__media-remove"
										@click.stop="clearMediaControl( index )"
									>
										<svg width="12" height="12" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.28564 14.192V3.42847H13.7142V14.192C13.7142 14.6685 13.5208 15.0889 13.1339 15.4533C12.747 15.8177 12.3005 15.9999 11.7946 15.9999H4.20529C3.69934 15.9999 3.25291 15.8177 2.866 15.4533C2.4791 15.0889 2.28564 14.6685 2.28564 14.192Z"></path><path d="M14.8571 1.14286V2.28571H1.14282V1.14286H4.57139L5.56085 0H10.4391L11.4285 1.14286H14.8571Z"></path></svg>
									</div>
								</div>
								<svg v-else xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="jet-engine-component-settings__media-add"><path d="M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm0-2c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm6 13h-5v5h-2v-5h-5v-2h5v-5h2v5h5v2z"/></svg>
							</div>
						</cx-vui-component-wrapper>
					</cx-vui-repeater-item>
				</cx-vui-repeater>
				<cx-vui-repeater
					button-label="<?php _e( 'New Control', 'jet-engine' ); ?>"
					button-style="accent-border"
					button-size="mini"
					v-model="settings.component_style_controls_list"
					v-if="'style' === componentControlsMode"
					@add-new-item="addNewControl( 'component_style_controls_list', {
						control_label: '',
						control_name: '',
						control_default: '',
					} )"
				>
					<cx-vui-repeater-item
						v-for="( control, index ) in settings.component_style_controls_list"
						:title="control.control_label"
						:subtitle="control.control_name"
						:collapsed="isCollapsed( control )"
						:index="index"
						@clone-item="cloneControl( $event, 'component_style_controls_list' )"
						@delete-item="deleteControl( $event, 'component_style_controls_list' )"
						:key="control.id ? control.id : control.id = getRandomID()"
						:ref="'control' + control.id"
					>
						<cx-vui-input
							label="<?php _e( 'Control Label', 'jet-engine' ); ?>"
							description="<?php _e( 'Control label to show in the component UI in editor', 'jet-engine' ); ?>"
							:wrapper-css="[ 'vertical-fullwidth' ]"
							size="fullwidth"
							:value="control.control_label"
							@input="setControlProp( 'component_style_controls_list', 'control_label', $event, index )"
							@on-input-change="preSetControlName( index, 'component_style_controls_list' )"
						></cx-vui-input>
						<cx-vui-input
							label="<?php _e( 'Control Name', 'jet-engine' ); ?>"
							description="<?php _e( 'Control key/name to save into the DB. Please use only lowercase letters, numbers and `_`. Also please note - name must be unique for this component (for both - styles and controls)', 'jet-engine' ); ?>"
							:wrapper-css="[ 'vertical-fullwidth' ]"
							size="fullwidth"
							:value="control.control_name"
							@input="setControlProp( 'component_style_controls_list', 'control_name', $event, index )"
						></cx-vui-input>
						<cx-vui-input
							label="<?php _e( 'Default Value', 'jet-engine' ); ?>"
							description="<?php _e( 'Default value of the given control', 'jet-engine' ); ?>"
							:wrapper-css="[ 'vertical-fullwidth' ]"
							size="fullwidth"
							:value="control.control_default"
							@input="setControlProp( 'component_style_controls_list', 'control_default', $event, index )"
							v-if="'media' !== control.control_type"
						><jet-engine-timber-css-vars-helper
							button-label="<?php esc_html_e( 'Use CSS Variable', 'jet-engine' ); ?>"
							@input="setControlProp( 'component_style_controls_list', 'control_default', $event, index )"
						></jet-engine-timber-css-vars-helper></cx-vui-input>
					</cx-vui-repeater-item>
				</cx-vui-repeater>
			</div>
		</script>
		<?php
	}

	/**
	 * Add components link to the views tab
	 * 
	 * @param  [type] $links  [description]
	 * @param  [type] $counts [description]
	 * @return [type]         [description]
	 */
	public function component_view_link( $links, $counts, $base_url ) {

		$links['component'] = sprintf(
			'<a href="%1$s" %3$s>%2$s <span class="count">(%4$s)</span></a>',
			add_query_arg( [ 'entry_type' => 'component' ], $base_url ),
			esc_html__( 'Components', 'jet-engine' ),
			( ! empty( $_GET['entry_type'] ) && 'component' === $_GET['entry_type'] ) ? 'class="current" aria-current="page"' : '',
			isset( $counts['component'] ) ? $counts['component']->posts_count : 0
		);

		return $links;
	}

	/**
	 * Editor assets
	 * 
	 * @return [type] [description]
	 */
	public function editor_assets() {

		/**
		 * Chek we're currently on Listing Items list page.
		 * Because 'jet-engine/templates/before-listing-assets' hook fires also in Elementor editor
		 * @see https://github.com/Crocoblock/issues-tracker/issues/13481
		 */
		if ( ! jet_engine()->listings->post_type->is_listings_edit_page() ) {
			return;
		}

		$module_data = jet_engine()->framework->get_included_module_data( 'cherry-x-vue-ui.php' );
		$ui          = new \CX_Vue_UI( $module_data );

		$ui->enqueue_assets();

		wp_enqueue_script(
			'jet-engine-listing-components',
			jet_engine()->listings->components->url( 'assets/js/components-editor.js' ),
			array( 'jquery', 'jet-plugins' ),
			jet_engine()->get_version(),
			true
		);

		$component_control_types = \Jet_Engine_Tools::prepare_list_for_js(
			jet_engine()->listings->components->get_supported_control_types(), ARRAY_A
		);

		wp_localize_script( 'jet-engine-listing-components', 'JetEngineComponentsData', [
			'component_control_types' => $component_control_types,
		] );
	}

	/**
	 * Add New Component button template to localized Listing data
	 */
	public function add_button_template( $data ) {

		/**
		 * Chek we're currently on Listing Items list page.
		 * Because 'jet-engine/templates/before-listing-assets' hook fires also in Elementor editor
		 * @see https://github.com/Crocoblock/issues-tracker/issues/13481
		 */
		if ( ! jet_engine()->listings->post_type->is_listings_edit_page() ) {
			return $data;
		}

		$data['addNewComponent'] = sprintf(
			'<a href="#" class="page-title-action is-new-component">%1$s</a>',
			__( 'Add New Component', 'jet-engine' )
		);

		add_action( 'admin_footer', [ $this, 'component_popup_template' ], 1000 );

		return $data;

	}

	/**
	 * Render new component popup template in footer
	 * 
	 * @return [type] [description]
	 */
	public function component_popup_template() {
		
		$action = jet_engine()->listings->post_type->admin_screen->get_listing_popup_action();
		$views  = jet_engine()->listings->post_type->get_listing_views();

		?>
		<div class="jet-listings-popup jet-listings-popup--new is-component-popup" style="display: none;">
			<div class="jet-listings-popup__overlay"></div>
			<div class="jet-listings-popup__content">
				<div class="jet-listings-popup__close">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><rect x="0" fill="none" width="20" height="20"/><g><path d="M14.95 6.46L11.41 10l3.54 3.54-1.41 1.41L10 11.42l-3.53 3.53-1.42-1.42L8.58 10 5.05 6.47l1.42-1.42L10 8.58l3.54-3.53z"/></g></svg>
				</div>
				<h3 class="jet-listings-popup__heading"><?php
					esc_html_e( 'Setup Component', 'jet-engine' );
				?></h3>
				<form class="jet-listings-popup__form is-component-popup" id="jet_engine_new_component" method="POST" action="<?php echo $action; ?>" >
					<input type="hidden" name="template_entry_type" value="component">
					<input type="hidden" name="listing_source" value="post">
					<div class="jet-listings-popup__form-row">
						<label for="component_template_name"><?php esc_html_e( 'Component name:', 'jet-engine' ); ?></label>
						<input type="text" id="component_template_name" name="template_name" placeholder="<?php esc_html_e( 'Set component name. Will be used as generated widget/block name.', 'jet-engine' ); ?>" value="" class="jet-listings-popup__control">
					</div>
					<div class="jet-listings-popup__form-row">
						<label for="component_view_type"><?php esc_html_e( 'Component view:', 'jet-engine' ); ?></label>
						<select id="component_view_type" name="listing_view_type" class="jet-listings-popup__control"><?php
							foreach ( $views as $view_key => $view_label ) {
								printf( 
									'<option value="%1$s">%2$s</option>',
									$view_key,
									$view_label
								);
							}
						?></select>
					</div>
					<div class="jet-listings-popup__form-actions">
						<button type="submit" class="button button-primary button-hero"><?php
							esc_html_e( 'Create Component', 'jet-engine' );
						?></button>
					</div>
				</form>
			</div>
		</div>
		<div class="jet-listings-popup jet-listings-popup--edit-settings is-component-popup jet-listings-popup--keep-alive" id="jet_engine_component_settings_popup" style="display: none;">
			<div class="jet-listings-popup__overlay"></div>
			<div class="jet-listings-popup__content">
				<div class="jet-listings-popup__close">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><rect x="0" fill="none" width="20" height="20"/><g><path d="M14.95 6.46L11.41 10l3.54 3.54-1.41 1.41L10 11.42l-3.53 3.53-1.42-1.42L8.58 10 5.05 6.47l1.42-1.42L10 8.58l3.54-3.53z"/></g></svg>
				</div>
				<div class="jet-engine-component-settings__header">
					<h3 class="jet-listings-popup__heading"><?php
						esc_html_e( 'Setup Component', 'jet-engine' );
					?></h3>
					<div class="jet-engine-component-settings__actions">
						<button type="button" class="button button-primary jet-engine-component-save"><?php
							_e( 'Save', 'jet-engine' );
						?></button>
						<button type="button" class="button button-secondary jet-engine-component-save open-editor"><?php
							_e( 'Save & Go To Editor', 'jet-engine' );
						?></button>
					</div>
				</div>
				<div id="jet_engine_component_settings_content"></div>
			</div>
		</div>
		<?php
	}

}
