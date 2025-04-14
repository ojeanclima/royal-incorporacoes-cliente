<div
	class="jet-smart-filters-settings-page jet-smart-filters-settings-page__ajax-request-type"
>
	<div class="ajax-request-types">
		<div class="ajax-request-types__header">
			<div class="cx-vui-title"><?php _e( 'Ajax Request Type', 'jet-smart-filters' ); ?></div>
			<div class="cx-vui-subtitle"><?php _e( 'This option allows you to set global WordPress variables and variables from the URL in the same way as on the page from which the request was sent. Use this option if the macros or dynamic tags in your query settings don’t work properly.', 'jet-smart-filters' ); ?></div>
			<cx-vui-radio
				name="ajax-request-types"
				v-model="settings.ajax_request_types"
				:optionsList="data.ajax_request_types_options"
			>
			</cx-vui-radio>
		</div>
	</div>
	<div class="ajax-request-signature">
		<cx-vui-switcher
			label="<?php _e( 'Add signatures to filters requests', 'jet-smart-filters' ); ?>"
			description="<?php _e( 'Enable this option to add and verify signature of each filters request. It makes your requests more secure.', 'jet-smart-filters' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			return-true="true"
			return-false="false"
			v-model="settings.use_signature_verification"
		>
		</cx-vui-switcher>
	</div>
</div>
