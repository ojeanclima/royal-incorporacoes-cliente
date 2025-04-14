<div class="jet-engine-timber-editor">
	<div class="jet-engine-timber-editor__header">
		<div id="titlediv" class="jet-engine-timber-editor__header-input">
			<input type="text" v-model="postTitle" id="title" class="jet-engine-timber-editor__title">
		</div>
		<div class="jet-engine-timber-editor__header-actions">
			<jet-engine-timber-settings
				:listing-id="postID"
				v-model="settings"
			/>
			<cx-vui-button
				button-style="accent"
				class="cx-vui-button--size-header-action"
				@click="save"
				:disabled="saving"
			><span slot="label"><?php _e( 'Save', 'jet-engine' ); ?></span></cx-vui-button>
		</div>
	</div>
	<div class="jet-engine-timber-editor-top-bar">
		<jet-engine-timber-presets
			@insert="applyPreset"
		></jet-engine-timber-presets>
		<div class="jet-engine-timber-editor-preview-settings">
			<label for="jet_engine_timber_editor_preview_width"><?php
				_e( 'Preview width:', 'jet-engine' );
			?></label>
			<input
				id="jet_engine_timber_editor_preview_width"
				class="jet-engine-timber-editor-preview-width-control"
				type="number"
				min="10"
				step="1"
				v-model="previewSettings.width"
			>
			<select class="jet-engine-timber-editor-preview-units-control" v-model="previewSettings.units">
				<option value="%">%</option>
				<option value="px">px</option>
				<option value="vw">vw</option>
			</select>
		</div>
	</div>
	<div class="jet-engine-timber-editor__body">
		<div 
			class="jet-engine-timber-editor__data"
			:style="{
				width: 'calc( 100% - ' + getPreviewWidth() + ' )',
				flex: '0 0 calc( 100% - ' + getPreviewWidth() + ' )',
			}"
		>
			<div class="jet-engine-timber-editor__data-control">
				<div class="jet-engine-timber-editor__group-title">
					<div class="jet-engine-timber-editor__group-title-text">HTML</div>
					<div class="jet-engine-timber-editor__group-title-actions">
						<jet-engine-timber-dynamic-data 
							@insert="insertDynamicData"
							mode="functions"
						>
							{ } &nbsp;<?php _e( 'Dynamic data', 'jet-engine' ); ?>
						</jet-engine-timber-dynamic-data>
						<jet-engine-timber-dynamic-data 
							@insert="insertDynamicData"
							mode="filters"
						>
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20"><g><path d="M13.11 4.36L9.87 7.6 8 5.73l3.24-3.24c.35-.34 1.05-.2 1.56.32.52.51.66 1.21.31 1.55zm-8 1.77l.91-1.12 9.01 9.01-1.19.84c-.71.71-2.63 1.16-3.82 1.16H6.14L4.9 17.26c-.59.59-1.54.59-2.12 0-.59-.58-.59-1.53 0-2.12l1.24-1.24v-3.88c0-1.13.4-3.19 1.09-3.89zm7.26 3.97l3.24-3.24c.34-.35 1.04-.21 1.55.31.52.51.66 1.21.31 1.55l-3.24 3.25z" fill="currentColor"/></g></svg>
							<?php _e( 'Filter data', 'jet-engine' ); ?>
						</jet-engine-timber-dynamic-data>
						<?php do_action( 'jet-engine/twig-views/editor/custom-actions', $this ); ?>
					</div>
				</div>
				<textarea ref="html" :value="html" class="jet-engine-timber-editor__data-control-input"></textarea>
			</div>
			<div class="jet-engine-timber-editor__data-control">
				<div class="jet-engine-timber-editor__group-title">
					<div class="jet-engine-timber-editor__group-title-text">
						CSS
						<jet-engine-timber-css-vars-helper
							button-label="var()"
							:merge-vars="instanceVars()"
							@input="insertCSSVar"
						></jet-engine-timber-css-vars-helper>
					</div>
					<div class="jet-engine-timber-editor__group-title-actions">
						<div class="jet-engine-timber-editor__group-title-notice"><?php
							printf( __( '* Use %s statement before each CSS selector to make it unique for current listing', 'jet-engine' ), '<code>selector</code>' );
						?></div>
					</div>
				</div>
				<textarea ref="css" :value="css" class="jet-engine-timber-editor__data-control-input"></textarea>
			</div>
		</div>
		<div 
			class="jet-engine-timber-editor__preview"
			:style="{
				width: getPreviewWidth(),
				flex: '0 0 ' + getPreviewWidth(),
			}"
		>
			<div class="jet-engine-timber-editor__group-title">
				<div class="jet-engine-timber-editor__group-title-text">Preview</div>
				<div class="jet-engine-timber-editor__group-title-actions">
					<cx-vui-button
						button-style="accent-border"
						@click="reloadPreview"
						size="mini"
					>
						<svg slot="label" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path d="M20.944 12.979c-.489 4.509-4.306 8.021-8.944 8.021-2.698 0-5.112-1.194-6.763-3.075l1.245-1.633c1.283 1.645 3.276 2.708 5.518 2.708 3.526 0 6.444-2.624 6.923-6.021h-2.923l4-5.25 4 5.25h-3.056zm-15.864-1.979c.487-3.387 3.4-6 6.92-6 2.237 0 4.228 1.059 5.51 2.698l1.244-1.632c-1.65-1.876-4.061-3.066-6.754-3.066-4.632 0-8.443 3.501-8.941 8h-3.059l4 5.25 4-5.25h-2.92z" fill="currentColor"/></svg>
						<span slot="label">&nbsp;<?php _e( 'Reload preview', 'jet-engine' ); ?></span>
					</cx-vui-button>
				</div>
			</div>
			<jet-style :listing-id="postID" :css-vars="instanceVars( true )">{{ css }}</jet-style>
			<div 
				:class="[ 'jet-engine-timber-editor__preview-body', 'jet-listing-' + postID ]"
				:style="reloadingStyles()"
				ref="previewBody"
				v-html="previewHTML"
			></div>
		</div>
	</div>
</div>