<div
	class="jet-engine-edit-page jet-data-table-page jet-engine-edit-page--loading"
	:class="{
		'jet-engine-edit-page--loaded': true,
	}"
>
	<div class="jet-engine-edit-page__fields">
		<div class="cx-vui-panel">
			<cx-vui-tabs
				:in-panel="false"
				value="general"
				layout="vertical"
			>
				<cx-vui-tabs-panel
					name="general"
					label="<?php _e( 'General Settings', 'jet-engine' ); ?>"
					key="general"
				>
					<cx-vui-input
						label="<?php _e( 'Name', 'jet-engine' ); ?>"
						description="<?php _e( 'Name of Table will be shown in the admin menu', 'jet-engine' ); ?>"
						:wrapper-css="[ 'equalwidth' ]"
						:size="'fullwidth'"
						:error="errors.name"
						v-model="generalSettings.name"
						@on-focus="handleFocus( 'name' )"
					></cx-vui-input>
					<cx-vui-select
						label="<?php _e( 'Data Query', 'jet-engine' ); ?>"
						description="<?php _e( 'Select previously created Query to get data from', 'jet-engine' ); ?>"
						:wrapper-css="[ 'equalwidth' ]"
						:options-list="queries"
						:size="'fullwidth'"
						v-model="generalSettings.query_id"
					>
						<div>
							<a v-if="generalSettings.query_id" target="_blank" :href="'<?php echo admin_url( 'admin.php?page=jet-engine-query&query_action=edit&id=' ) ?>' + generalSettings.query_id"><?php
								_e( 'Edit selected query', 'jet-engine' );
							?></a><span v-if="generalSettings.query_id">&nbsp;<?php _e( 'or', 'jet-engine' ); ?>&nbsp;</span><a target="_blank" href="<?php echo admin_url( 'admin.php?page=jet-engine-query&query_action=add' ); ?>"><?php
								_e( 'Create new query', 'jet-engine' );
							?></a>
						</div>
					</cx-vui-select>
					<cx-vui-component-wrapper
						label="<?php _e( 'Fetch the data', 'jet-engine' ); ?>"
						description="<?php _e( 'Fetch sample of the data to find all possible columns and preview the table', 'jet-engine' ); ?>"
						:wrapper-css="[ 'equalwidth' ]"
					>
						<cx-vui-button
							button-style="accent"
							size="mini"
							:loading="fetchingData"
							@click="makeFetchDataRequest"
						>
							<span
								slot="label"
								v-if="! generalSettings.allowed_columns"
							><?php _e( 'Fetch Data', 'jet-engine' ); ?></span>
							<span
								slot="label"
								v-else
							><?php _e( 'Re-fetch Data', 'jet-engine' ); ?></span>
						</cx-vui-button>
					</cx-vui-component-wrapper>
					<cx-vui-component-wrapper
						v-if="generalSettings.allowed_columns && ! generalSettings.allowed_columns.length"
						label="<?php _e( 'Note!', 'jet-engine' ); ?>"
						description="<?php _e( 'We have not found any visible columns in the fetched data. You can process & build the table, but the `Fetched columns` data source won`t be available for the table columns.', 'jet-engine' ); ?>"
					>
					</cx-vui-component-wrapper>
				</cx-vui-tabs-panel>
				<cx-vui-tabs-panel
					name="columns"
					label="<?php _e( 'Columns', 'jet-engine' ); ?>"
					key="columns"
				>
					<jet-table-columns
						v-model="metaFields"
						:allowed-columns="generalSettings.allowed_columns"
						:key="fieldsLoaded"
					></jet-table-columns>
				</cx-vui-tabs-panel>
				<cx-vui-tabs-panel
					name="styles"
					label="<?php _e( 'Table Styles', 'jet-engine' ); ?>"
					key="styles"
				>
					<cx-vui-component-wrapper
						label="<?php _e( 'Note!', 'jet-engine' ); ?>"
						description="<?php _e( 'Advanced styling for the table you`ll can set when output table on the front-end with Gutenberg block or Elementor widget or Bricks element', 'jet-engine' ); ?>"
					></cx-vui-component-wrapper>
					<div class="jet-engine-dynamic-table-styles">
						<div class="jet-engine-dynamic-table-styles__item" v-for="field in metaFields">
							<cx-vui-collapse
								:collapsed="true"
							>
								<div slot="title">{{ field.name }}</div>
								<div slot="content">
									<cx-vui-input
										label="<?php _e( 'Width', 'jet-engine' ); ?>"
										description="<?php _e( 'Column max width. Accepts any CSS-supported string. Example: 100px, 20%, 10vw', 'jet-engine' ); ?>"
										:wrapper-css="[ 'equalwidth' ]"
										size="fullwidth"
										v-model="generalSettings.inline_styles[ field._id ].width"
									></cx-vui-input>
									<jet-table-switcher
										s-label="<?php _e( 'Heading', 'jet-engine' ); ?>"
										e-label="<?php _e( 'Cell', 'jet-engine' ); ?>"
									>
										<div slot="state-s">
											<cx-vui-select
												label="<?php _e( 'Vertical Align', 'jet-engine' ); ?>"
												description="<?php _e( 'Vertical align of the heading cell', 'jet-engine' ); ?>"
												:options-list="[
													{
														value: '',
														label: '<?php _e( 'Select...', 'jet-engine' ); ?>',
													},
													{
														value: 'top',
														label: '<?php _e( 'Top', 'jet-engine' ); ?>',
													},
													{
														value: 'middle',
														label: '<?php _e( 'Middle', 'jet-engine' ); ?>',
													},
													{
														value: 'bottom',
														label: '<?php _e( 'Bottom', 'jet-engine' ); ?>',
													},
												]"
												:wrapper-css="[ 'equalwidth' ]"
												size="fullwidth"
												v-model="generalSettings.inline_styles[ field._id ].h_v_align"
											></cx-vui-select>
											<cx-vui-select
												label="<?php _e( 'Horizontal Align', 'jet-engine' ); ?>"
												description="<?php _e( 'Horizontal align of the heading cell', 'jet-engine' ); ?>"
												:wrapper-css="[ 'equalwidth' ]"
												size="fullwidth"
												:options-list="[
													{
														value: '',
														label: '<?php _e( 'Select...', 'jet-engine' ); ?>',
													},
													{
														value: 'left',
														label: '<?php _e( 'Left', 'jet-engine' ); ?>',
													},
													{
														value: 'center',
														label: '<?php _e( 'Center', 'jet-engine' ); ?>',
													},
													{
														value: 'right',
														label: '<?php _e( 'Right', 'jet-engine' ); ?>',
													},
												]"
												v-model="generalSettings.inline_styles[ field._id ].h_h_align"
											></cx-vui-select>
										</div>
										<div slot="state-e">
											<cx-vui-select
												label="<?php _e( 'Vertical Align', 'jet-engine' ); ?>"
												description="<?php _e( 'Vertical align of the regular cell', 'jet-engine' ); ?>"
												:wrapper-css="[ 'equalwidth' ]"
												size="fullwidth"
												:options-list="[
													{
														value: '',
														label: '<?php _e( 'Select...', 'jet-engine' ); ?>',
													},
													{
														value: 'top',
														label: '<?php _e( 'Top', 'jet-engine' ); ?>',
													},
													{
														value: 'middle',
														label: '<?php _e( 'Middle', 'jet-engine' ); ?>',
													},
													{
														value: 'bottom',
														label: '<?php _e( 'Bottom', 'jet-engine' ); ?>',
													},
												]"
												v-model="generalSettings.inline_styles[ field._id ].c_v_align"
											></cx-vui-select>
											<cx-vui-select
												label="<?php _e( 'Horizontal Align', 'jet-engine' ); ?>"
												description="<?php _e( 'Horizontal align of the regular cell', 'jet-engine' ); ?>"
												:wrapper-css="[ 'equalwidth' ]"
												size="fullwidth"
												:options-list="[
													{
														value: '',
														label: '<?php _e( 'Select...', 'jet-engine' ); ?>',
													},
													{
														value: 'left',
														label: '<?php _e( 'Left', 'jet-engine' ); ?>',
													},
													{
														value: 'center',
														label: '<?php _e( 'Center', 'jet-engine' ); ?>',
													},
													{
														value: 'right',
														label: '<?php _e( 'Right', 'jet-engine' ); ?>',
													},
												]"
												v-model="generalSettings.inline_styles[ field._id ].c_h_align"
											></cx-vui-select>
										</div>
									</jet-table-switcher>
								</div>
							</cx-vui-collapse>
						</div>
					</div>
				</cx-vui-tabs-panel>
			</cx-vui-tabs>
		</div>
	</div>
	<div class="jet-engine-edit-page__actions">
		<div class="jet-engine-edit-page__actions-panel">
			<div class="jet-engine-edit-page__actions-buttons">
				<div class="jet-engine-edit-page__actions-save">
					<cx-vui-button
						:button-style="'accent'"
						:custom-css="'fullwidth'"
						:loading="saving"
						@click="save"
					>
						<svg slot="label" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.6667 5.33333V1.79167H1.79167V5.33333H10.6667ZM6.125 13.4167C6.65278 13.9444 7.27778 14.2083 8 14.2083C8.72222 14.2083 9.34722 13.9444 9.875 13.4167C10.4028 12.8889 10.6667 12.2639 10.6667 11.5417C10.6667 10.8194 10.4028 10.1944 9.875 9.66667C9.34722 9.13889 8.72222 8.875 8 8.875C7.27778 8.875 6.65278 9.13889 6.125 9.66667C5.59722 10.1944 5.33333 10.8194 5.33333 11.5417C5.33333 12.2639 5.59722 12.8889 6.125 13.4167ZM12.4583 0L16 3.54167V14.2083C16 14.6806 15.8194 15.0972 15.4583 15.4583C15.0972 15.8194 14.6806 16 14.2083 16H1.79167C1.29167 16 0.861111 15.8194 0.5 15.4583C0.166667 15.0972 0 14.6806 0 14.2083V1.79167C0 1.31944 0.166667 0.902778 0.5 0.541667C0.861111 0.180556 1.29167 0 1.79167 0H12.4583Z" fill="white"/></svg>
						<span slot="label">{{ buttonLabel }}</span>
					</cx-vui-button>
				</div>
				<div
					class="jet-engine-edit-page__actions-delete"
					v-if="isEdit"
				>
					<cx-vui-button
						:button-style="'link-error'"
						:size="'link'"
						@click="showDeleteDialog = true"
					>
						<svg slot="label" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.28564 14.1921V3.42857H13.7142V14.1921C13.7142 14.6686 13.5208 15.089 13.1339 15.4534C12.747 15.8178 12.3005 16 11.7946 16H4.20529C3.69934 16 3.25291 15.8178 2.866 15.4534C2.4791 15.089 2.28564 14.6686 2.28564 14.1921Z"/><path d="M14.8571 1.14286V2.28571H1.14282V1.14286H4.57139L5.56085 0H10.4391L11.4285 1.14286H14.8571Z"/></svg>
						<span slot="label"><?php _e( 'Delete', 'jet-engine' ); ?></span>
					</cx-vui-button>
				</div>
			</div>
			<div
				class="jet-engine-edit-page__notice-error"
				v-if="this.errorNotices.length"
			>
				<div class="jet-engine-edit-page__notice-error-content">
					<div class="jet-engine-edit-page__notice-error-items">
						<div
							v-for="( notice, index ) in errorNotices"
							:key="'notice_' + index"
						>{{ notice }}</div>
					</div>
				</div>
			</div>
			<div class="cx-vui-hr"></div>
			<div class="cx-vui-subtitle jet-engine-help-list-title"><?php _e( 'Need Help?', 'jet-engine' ); ?></div>
			<div class="cx-vui-panel">
				<div class="jet-engine-help-list">
					<div class="jet-engine-help-list__item" v-for="link in helpLinks">
						<a :href="link.url" target="_blank">
							<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.4413 7.39906C10.9421 6.89828 11.1925 6.29734 11.1925 5.59624C11.1925 4.71987 10.8795 3.9687 10.2535 3.34272C9.62754 2.71674 8.87637 2.40376 8 2.40376C7.12363 2.40376 6.37246 2.71674 5.74648 3.34272C5.1205 3.9687 4.80751 4.71987 4.80751 5.59624H6.38498C6.38498 5.17058 6.54773 4.79499 6.87324 4.46948C7.19875 4.14398 7.57434 3.98122 8 3.98122C8.42566 3.98122 8.80125 4.14398 9.12676 4.46948C9.45227 4.79499 9.61502 5.17058 9.61502 5.59624C9.61502 6.02191 9.45227 6.3975 9.12676 6.723L8.15024 7.73709C7.52426 8.41315 7.21127 9.16432 7.21127 9.99061V10.4038H8.78873C8.78873 9.57747 9.10172 8.82629 9.7277 8.15024L10.4413 7.39906ZM8.78873 13.5962V12.0188H7.21127V13.5962H8.78873ZM2.32864 2.3662C3.9061 0.788732 5.79656 0 8 0C10.2034 0 12.0814 0.788732 13.6338 2.3662C15.2113 3.91862 16 5.79656 16 8C16 10.2034 15.2113 12.0939 13.6338 13.6714C12.0814 15.2238 10.2034 16 8 16C5.79656 16 3.9061 15.2238 2.32864 13.6714C0.776213 12.0939 0 10.2034 0 8C0 5.79656 0.776213 3.91862 2.32864 2.3662Z" fill="#007CBA"/></svg>
							{{ link.label }}
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<jet-table-delete-dialog
		v-if="showDeleteDialog"
		v-model="showDeleteDialog"
		:item-id="isEdit"
	></jet-table-delete-dialog>
	<div class="jet-dynamic-table-preview">
		<cx-vui-button
			button-style="accent"
			size="mini"
			@click="reloadPreview"
			:loading="isReloadingPreview"
		>
			<span slot="label"><?php _e( 'Reload Preview', 'jet-engine' ); ?></span>
		</cx-vui-button>
		<div ref="preview" :class="{ 'jet-dynamic-table-reloading-preview': isReloadingPreview }" :style="{ overflow: 'auto' }"></div>
	</div>
</div>
