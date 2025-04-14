<div
	class="jet-smart-filters-settings-page jet-smart-filters-settings-page__seo-rules cx-vui-component-wrapper"
>
	<cx-vui-switcher
		name="use_seo_sitemap"
		label="<?php _e( 'Use SEO & Sitemap Rules', 'jet-smart-filters' ); ?>"
		description="<?php _e( 'Configure rules to generate filters specific sitemaps', 'jet-smart-filters' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		v-model="settings.use_seo_sitemap">
	</cx-vui-switcher>
	<div
		class="seo-sitemap-rules"
		v-if="settings.use_seo_sitemap"
	>
		<cx-vui-repeater
			class="seo-sitemap-rules-repeater"
			:class="{'repeater-one-item': seoSitemapRulesSettings.length === 1}"
			name="seo-sitemap-rules"
			buttonLabel="<?php _e( 'Add Rule', 'jet-smart-filters' ); ?>"
			buttonSize="mini"
			v-model="seoSitemapRulesSettings"
			@add-new-item="repeaterAddItem(
				{
					url: '',
					provider: '',
					query_id: '',
					filters: [],
					title: '',
					description: ''
				},
				seoSitemapRulesSettings
			)"
		>
			<cx-vui-repeater-item
				calss="seo-sitemap-rules-repeater-item"
				v-for="( rule, ruleIndex ) in seoSitemapRulesSettings"
				:index="ruleIndex"
				:title="getRuleTitle(ruleIndex)"
				:collapsed="repeaterItemCollapsed"
				@clone-item="repeaterCloneItem( ruleIndex, seoSitemapRulesSettings )"
				@delete-item="repeaterDeleteItem( ruleIndex, seoSitemapRulesSettings )"
			>
				<RuleItem
					v-model="rule"
				/>
			</cx-vui-repeater-item>
		</cx-vui-repeater>
		<div class="seo-sitemap-rules-actions">
			<cx-vui-button
				class="save-rules-button"
				:loading="seoSitemapRulesSaving"
				@click="saveRules"
			>
				<template v-slot:label>
					<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.6667 5.33333V1.79167H1.79167V5.33333H10.6667ZM6.125 13.4167C6.65278 13.9444 7.27778 14.2083 8 14.2083C8.72222 14.2083 9.34722 13.9444 9.875 13.4167C10.4028 12.8889 10.6667 12.2639 10.6667 11.5417C10.6667 10.8194 10.4028 10.1944 9.875 9.66667C9.34722 9.13889 8.72222 8.875 8 8.875C7.27778 8.875 6.65278 9.13889 6.125 9.66667C5.59722 10.1944 5.33333 10.8194 5.33333 11.5417C5.33333 12.2639 5.59722 12.8889 6.125 13.4167ZM12.4583 0L16 3.54167V14.2083C16 14.6806 15.8194 15.0972 15.4583 15.4583C15.0972 15.8194 14.6806 16 14.2083 16H1.79167C1.29167 16 0.861111 15.8194 0.5 15.4583C0.166667 15.0972 0 14.6806 0 14.2083V1.79167C0 1.31944 0.166667 0.902778 0.5 0.541667C0.861111 0.180556 1.29167 0 1.79167 0H12.4583Z" fill="white"/></svg>
					<span><?php _e( 'Save Rules', 'jet-smart-filters' ); ?></span>
				</template>
			</cx-vui-button>
			<div
				class="seo-sitemap-rules-xml-path"
				:class="copyXmlUrlClass"
			>
				<input class="seo-sitemap-rules-xml-path-text" readonly="" :value="xmlUrl">
				<button
					class="seo-sitemap-rules-xml-path-btn"
					@click="copyXmlUrl"
				>
					<svg class="seo-sitemap-rules-icon-copy" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg">
						<path d="M18.984 21v-14.016h-10.969v14.016h10.969zM18.984 5.016q0.797 0 1.406 0.586t0.609 1.383v14.016q0 0.797-0.609 1.406t-1.406 0.609h-10.969q-0.797 0-1.406-0.609t-0.609-1.406v-14.016q0-0.797 0.609-1.383t1.406-0.586h10.969zM15.984 0.984v2.016h-12v14.016h-1.969v-14.016q0-0.797 0.586-1.406t1.383-0.609h12z"></path>
					</svg>
					<svg class="seo-sitemap-rules-icon-copy-success" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg">
						<path d="M9 16.219l10.594-10.641 1.406 1.406-12 12-5.578-5.578 1.359-1.406z"></path>
					</svg>
					<svg class="seo-sitemap-rules-icon-copy-error" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg">
						<path d="M12 20.016q3.281 0 5.648-2.367t2.367-5.648-2.367-5.648-5.648-2.367-5.648 2.367-2.367 5.648 2.367 5.648 5.648 2.367zM12 2.016q4.125 0 7.055 2.93t2.93 7.055-2.93 7.055-7.055 2.93-7.055-2.93-2.93-7.055 2.93-7.055 7.055-2.93zM11.016 6.984h1.969v6h-1.969v-6zM11.016 15h1.969v2.016h-1.969v-2.016z"></path>
					</svg>
				</button>
			</div>
		</div>
	</div>
</div>
