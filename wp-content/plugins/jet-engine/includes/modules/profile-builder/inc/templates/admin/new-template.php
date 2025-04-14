<?php
/**
 * New template component
 */
?>
<div class="jet-profile-template">
	<a
		href="#"
		class="jet-profile-template__trigger"
		style="text-decoration-style: dashed;"
		@click.prevent="showPopup = true"
	>+ <?php _e( 'Create New Template', 'jet-engine' ); ?></a>
	<cx-vui-popup
		v-model="showPopup"
		body-width="600px"
		:footer="false"
		@on-cancel="closePopup"
		@on-ok="createTemplate"
		ok-label="<?php _e( 'Create Template', 'jet-engine' ); ?>"
	>
		<div class="cx-vui-subtitle" slot="title"><?php _e( 'Create New Profile Template', 'jet-engine' ); ?></div>
		<div slot="content">
			<cx-vui-input
				label="<?php _e( 'Template Name', 'jet-engine' ); ?>"
				description="<?php _e( 'Name of new template', 'jet-engine' ); ?>"
				:wrapper-css="[ 'equalwidth', 'collpase-sides' ]"
				size="fullwidth"
				:error="hasNameError"
				v-model="templateName"
			></cx-vui-input>
			<cx-vui-select
				label="<?php _e( 'Template Type', 'jet-engine' ); ?>"
				description="<?php _e( 'Where you want to create this template', 'jet-engine' ); ?>"
				:wrapper-css="[ 'equalwidth', 'collpase-sides' ]"
				size="fullwidth"
				:options-list="templateSources()"
				v-model="templateType"
			></cx-vui-select>
			<cx-vui-select
				label="<?php _e( 'Listing view', 'jet-engine' ); ?>"
				description="<?php _e( 'What we will use to build listing template', 'jet-engine' ); ?>"
				v-if="listingSource === templateType"
				:wrapper-css="[ 'equalwidth', 'collpase-sides' ]"
				size="fullwidth"
				:options-list="templateViews()"
				v-model="templateView"
			></cx-vui-select>
			<cx-vui-button
				button-style="accent"
				size="mini"
				:disabled="creating"
				@click="createTemplate"
			><span slot="label"><?php _e( 'Create Template', 'jet-engine' ); ?></span></cx-vui-button>&nbsp;&nbsp;&nbsp;
			<cx-vui-button
				size="mini"
				@click="closePopup"
				:disabled="creating"
			><span slot="label"><?php _e( 'Cancel', 'jet-engine' ); ?></span></cx-vui-button>
		</div>
	</cx-vui-popup>
</div>