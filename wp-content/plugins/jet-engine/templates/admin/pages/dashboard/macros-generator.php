<div class="jet-engine-shortcode-generator">
	<div class="jet-engine-shortcode-generator__fields">
		<cx-vui-select
			label="<?php _e( 'Macros', 'jet-engine' ); ?>"
			description="<?php _e( 'Select macros to paste', 'jet-engine' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:options-list="macrosList"
			size="fullwidth"
			v-model="result.macros"
		></cx-vui-select>
		<component
			v-for="control in macrosControls"
			:is="control.type"
			:key="controlKey( control )"
			:options-list="control.optionsList"
			:description="control.description"
			:groups-list="control.groupsList"
			:default="control.default"
			:label="control.label"
			:wrapper-css="[ 'equalwidth' ]"
			size="fullwidth"
			v-if="checkCondition( control.condition, result.macros )"
			v-model="result[ result.macros ][ control.name ]"
		/>
		<cx-vui-select
			label="<?php _e( 'Context', 'jet-engine' ); ?>"
			description="<?php _e( 'Defines object to get macros data from', 'jet-engine' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:options-list="contextList"
			size="fullwidth"
			v-model="result.advancedSettings.context"
		></cx-vui-select>
		<cx-vui-input
			label="<?php _e( 'Fallback', 'jet-engine' ); ?>"
			description="<?php _e( 'Value to return if macros itself returns an empty value', 'jet-engine' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			size="fullwidth"
			v-model="result.advancedSettings.fallback"
		></cx-vui-input>
		<cx-vui-switcher
			label="<?php _e( 'Bricks Dynamic Macro', 'jet-engine' ); ?>"
			description="<?php _e( 'I will use this macro in Bricks builder dynamic tag.', 'jet-engine' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			v-if="isBricksMacroVisible"
			v-model="isBricksMacroEnabled"
		></cx-vui-switcher>
	</div>
	<div class="jet-shortocde-generator__result">
		{{ generatedMacros }}
		<div
			class="jet-shortocde-generator__result-copy"
			role="button"
			v-if="showCopy"
			@click="copyToClipboard"
		>
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24px" height="24px"><path d="M0 0h24v24H0z" fill="none"/><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
			<div
				class="cx-vui-tooltip"
				v-if="copied"
			>
				<?php _e( 'Copied!', 'jet-engine' ); ?>
			</div>
		</div>
	</div>
	<cx-vui-component-wrapper
		label="<?php _e( 'Warning:', 'jet-engine' ); ?>"
		description="<?php _e( "Please note: don't use these symbols in macro arguments: &quot;, :, {, }. Bricks builder can't correctly parse these symbols in dynamic tags.", 'jet-engine' ); ?>"
		v-if="isBricksMacroEnabled"
	></cx-vui-component-wrapper>
</div>