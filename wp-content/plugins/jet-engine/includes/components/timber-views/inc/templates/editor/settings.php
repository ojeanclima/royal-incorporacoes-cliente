<div
	class="jet-engine-timber-settings"
	@keydown.esc="closePopup"
>
	<button type="button" class="jet-engine-timber-settings__trigger" @click="switchPopup">
		<svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M13.9498 8.78C13.9798 8.53 13.9998 8.27 13.9998 8C13.9998 7.73 13.9798 7.47 13.9398 7.22L15.6298 5.9C15.7798 5.78 15.8198 5.56 15.7298 5.39L14.1298 2.62C14.0298 2.44 13.8198 2.38 13.6398 2.44L11.6498 3.24C11.2298 2.92 10.7898 2.66 10.2998 2.46L9.99976 0.34C9.96976 0.14 9.79976 0 9.59976 0H6.39976C6.19976 0 6.03976 0.14 6.00976 0.34L5.70976 2.46C5.21976 2.66 4.76976 2.93 4.35976 3.24L2.36976 2.44C2.18976 2.37 1.97976 2.44 1.87976 2.62L0.279763 5.39C0.179763 5.57 0.219763 5.78 0.379763 5.9L2.06976 7.22C2.02976 7.47 1.99976 7.74 1.99976 8C1.99976 8.26 2.01976 8.53 2.05976 8.78L0.369763 10.1C0.219763 10.22 0.179763 10.44 0.269763 10.61L1.86976 13.38C1.96976 13.56 2.17976 13.62 2.35976 13.56L4.34976 12.76C4.76976 13.08 5.20976 13.34 5.69976 13.54L5.99976 15.66C6.03976 15.86 6.19976 16 6.39976 16H9.59976C9.79976 16 9.96976 15.86 9.98976 15.66L10.2898 13.54C10.7798 13.34 11.2298 13.07 11.6398 12.76L13.6298 13.56C13.8098 13.63 14.0198 13.56 14.1198 13.38L15.7198 10.61C15.8198 10.43 15.7798 10.22 15.6198 10.1L13.9498 8.78ZM7.99976 11C6.34976 11 4.99976 9.65 4.99976 8C4.99976 6.35 6.34976 5 7.99976 5C9.64976 5 10.9998 6.35 10.9998 8C10.9998 9.65 9.64976 11 7.99976 11Z"></path></svg>
		<?php _e( 'Settings', 'jet-engine' ); ?>
	</button>
	<div
		v-if="showPopup"
		class="jet-engine-timber-settings__popup"
		ref="popup"
		tabindex="-1"
	>
		<div class="jet-engine-timber-settings__popup-content">
			<div class="jet-engine-timber-settings__popup-close" @click="closePopup">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
					<rect x="0" fill="none" width="20" height="20"></rect><g><path d="M14.95 6.46L11.41 10l3.54 3.54-1.41 1.41L10 11.42l-3.53 3.53-1.42-1.42L8.58 10 5.05 6.47l1.42-1.42L10 8.58l3.54-3.53z"></path></g>
				</svg>
			</div>
			<div v-if="'component' === entryType">
				<div class="jet-engine-timber-settings__nav">
					<a
						href="#"
						:class="{ 
							'jet-engine-timber-settings__nav-link': true, 
							'is-active': 'content' === componentControlsMode
						}"
						@click.prevent="componentControlsMode = 'content'"
					><?php _e( 'Content Controls', 'jet-engine' ); ?></a>
					|
					<a
						href="#"
						:class="{ 
							'jet-engine-timber-settings__nav-link': true, 
							'is-active': 'style' === componentControlsMode
						}"
						@click.prevent="componentControlsMode = 'style'"
					><?php _e( 'Style Controls', 'jet-engine' ); ?></a>
				</div>
				<cx-vui-repeater
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
							:options-list="componentControlTypes"
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
								class="jet-engine-timber-settings__media"
								@click="openMediaFrame( control, index )"
							>
								<div
									class="jet-engine-timber-settings__media-preview"
									v-if="hasControlDefaultImage( control )"
								>
									<img
										:src="control.control_default_image.thumb"
										alt=""
									>
									<div
										class="jet-engine-timber-settings__media-remove"
										@click.stop="clearMediaControl( index )"
									>
										<svg width="12" height="12" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.28564 14.192V3.42847H13.7142V14.192C13.7142 14.6685 13.5208 15.0889 13.1339 15.4533C12.747 15.8177 12.3005 15.9999 11.7946 15.9999H4.20529C3.69934 15.9999 3.25291 15.8177 2.866 15.4533C2.4791 15.0889 2.28564 14.6685 2.28564 14.192Z"></path><path d="M14.8571 1.14286V2.28571H1.14282V1.14286H4.57139L5.56085 0H10.4391L11.4285 1.14286H14.8571Z"></path></svg>
									</div>
								</div>
								<svg v-else xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="jet-engine-timber-settings__media-add"><path d="M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm0-2c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm6 13h-5v5h-2v-5h-5v-2h5v-5h2v5h5v2z"/></svg>
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
			<div v-else>
			<?php
				echo $form;
			?></div>
		</div>
		<div class="jet-engine-timber-settings__popup-overlay" @click="closePopup"></div>
	</div>
</div>