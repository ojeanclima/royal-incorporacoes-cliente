<div class="seo-sitemap-rule">
	<cx-vui-input
		label="<?php _e( 'Page slug', 'jet-smart-filters' ); ?>"
		v-model="value.url"
	></cx-vui-input>
	<div class="seo-page-slug-example">Example: <del><?php echo trailingslashit( get_site_url() ); ?></del><strong>page-slug</strong></div>
	<div class="provider-data">
		<cx-vui-select
			label="<?php _e( 'Provider', 'jet-smart-filters' ); ?>"
			:optionsList="providersListOptions"
			v-model="value.provider"
		/>
		<cx-vui-input
			label="<?php _e( 'Query ID', 'jet-smart-filters' ); ?>"
			v-model="value.query_id"
		></cx-vui-input>
	</div>
	<jsf-repeater
		v-model="value.filters"
		label="<?php _e( 'Allowed Filters (max 3 filters)', 'jet-smart-filters' ); ?>"
		defaultItem="''"
		addText="<?php _e( 'Add Filter', 'jet-smart-filters' ); ?>"
		:actionsEnabled="!value.filters || value.filters.length < 3"
	>
		<template v-slot="{item, index:filterIndex}">
			<cx-vui-select
				:optionsList="getAllowedFiltersOptions(filterIndex)"
				placeholder="<?php _e( 'Filter', 'jet-smart-filters' ); ?>"
				v-model="value.filters[filterIndex]"
			/>
		</template>
	</jsf-repeater>
	<jsf-macro-input
		class="seo-rule-title"
		v-model="value.title"
		label="<?php _e( 'SEO Title', 'jet-smart-filters' ); ?>"
		:options="selectedFiltersMacroOptions"
	/>
	<jsf-macro-input
		class="seo-rule-description"
		v-model="value.description"
		label="<?php _e( 'SEO Description', 'jet-smart-filters' ); ?>"
		:options="selectedFiltersMacroOptions"
	/>
</div>