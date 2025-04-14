<div class="jet-ai-builder">
	<div class="jet-ai-builder__license-warning" v-if="!hasLicense">
		<h3 class="cx-vui-subtitle"><?php _e( 'Please note:', 'jet-engine' ); ?></h3>
		<div class="jet-ai-builder__license-warning-text">
			<p><?php printf(
				__( 'You need to %s your license to use AI functionality.', 'jet-engine' ),
				'<a href="' . admin_url( 'admin.php?page=jet-dashboard-license-page&subpage=license-manager' ) . '" traget="_blank">' . __( 'activate', 'jet-engine' ) . '</a>'
			); ?></p>
			<p><?php _e( 'Usage restrictions:', 'jet-engine' ); ?></p>
			<p><?php _e( '<b>All-inclusive Lifetime</b> and <b>Freelance Lifetime</b> Crocoblock subscription plans allows <b>90</b> AI requests per month.', 'jet-engine' ); ?></p>
			<p><?php _e( '<b>JetEngine</b> and <b>Yearly</b> subscriptions allows <b>30</b> AI requests per month.', 'jet-engine' ); ?></p>
		</div>
	</div>
	<div class="jet-ai-builder-nav">
		<a
			:href="pageURL()"
			:class="{
				'jet-ai-builder-nav__item': true,
				'jet-ai-builder-nav__item-current': 'create' === pageNow
			}"
		><?php _e( 'New Model', 'jet-engine' ); ?></a>
		|
		<a
			:href="pageURL( 'models' )"
			:class="{
				'jet-ai-builder-nav__item': true,
				'jet-ai-builder-nav__item-current': 'models' === pageNow
			}"
			@click.prevent="goToModels( event )"
		><?php _e( 'Previously Created Models', 'jet-engine' ); ?></a>
	</div>
	<jet-ai-website-builder-models
		v-if="'models' === pageNow"
	></jet-ai-website-builder-models>
	<div class="cx-vui-panel" v-if="'create' === pageNow">
		<div class="jet-ai-builder-container">
			<div class="jet-ai-builder-controls">
				<cx-vui-textarea
					label="<?php _e( 'What website do you want to build', 'jet-engine' ); ?>"
					description="<?php _e( 'Describe in 1-2 sentences what is your site about (topic and some general information).', 'jet-engine' ); ?>"
					size="fullwidth"
					rows="3"
					v-model="promptTopic"
				></cx-vui-textarea>
				<cx-vui-textarea
					label="<?php _e( 'What functionality do you need', 'jet-engine' ); ?>"
					description="<?php _e( 'Here your need to describe in 2-3 sentences what functionality parts you want to add and what tasks your website should resolve.', 'jet-engine' ); ?>"
					size="fullwidth"
					rows="3"
					v-model="promptFunctionality"
				></cx-vui-textarea>
				<div :class="{
					'jet-ai-builder-prompt-limit': true,
					'cx-vui-component': true,
					'jet-ai-builder-prompt-limit--error': ( promptLength >= promptLimit ),
				}">
					Your prompt length: {{ promptLength }}/{{ promptLimit }}
				</div>
				<div class="cx-vui-component">
					<div class="jet-ai-builder-actions">
						<cx-vui-button
							button-style="accent"
							@click="prepareModel()"
							:loading="isLoading"
							:disabled="! promptTopic || ! promptFunctionality || isCreatingModel"
						>
							<span
								slot="label"
								v-if="! jsonModel || ! jsonModel.postTypes"
							><?php
								_e( 'Preview Website Model', 'jet-engine' );
							?></span>
							<span
								slot="label"
								v-if="jsonModel && jsonModel.postTypes"
							><?php
								_e( 'Regenerate Website Model', 'jet-engine' );
							?></span>
						</cx-vui-button>
						<div class="jet-ai-builder-note" v-if="limit && usage">
							You're used <b>{{ usage }}</b> AI requests of <b>{{ limit }}</b> monthly quota
						</div>
					</div>
					<div
						v-if="error"
						class="cx-vui-inline-notice cx-vui-inline-notice--error"
					>{{ error }}</div>
				</div>
			</div>
			<div class="jet-ai-builder-hints">
				<h4 class="cx-vui-subtitle">How to use:</h4>
				<ul class="jet-ai-builder-hints__list">
					<li>Describe the main purpose of your website. Provide some details, but not too many. The description should be 1-2 sentences long. <b>Please use English for prompts</b> to ensure the best results.</li>
					<li>Describe the main functionality parts you need: what you want to filter, where you want to set relations, etc. Please note that at the moment, Website Structure Builder can't set up profiles and create forms. Keep this in mind when describing the required functionality.</li>
					<li>Click “Preview Model,” and Website Structure Builder will generate a basic data model for the requested functionality.</li>
					<li>Review the suggested model, and if it meets your needs, confirm the data model creation by clicking “Create Website Model.” If you need to make changes, modify the description and click “Regenerate Website Model.”</li>
					<li><b>Please note:</b> no data will be physically added to your website until you click “Create Website Model.” After clicking “Create Website Model,” all described entities will be physically created in your database.</li>
				</ul>
			</div>
		</div>
		<div
			class="jet-ai-builder-res"
			v-if="jsonModel && jsonModel.postTypes"
		>
			<div class="jet-ai-builder-section">
				<h4 class="cx-vui-subtitle"><?php _e( 'Custom Post Types:', 'jet-engine' ) ?></h4>
				<div class="jet-ai-builder-note v-indent" v-if="! jsonModel.postTypes || ! Object.keys( jsonModel.postTypes ).length">
					<?php _e( 'There is no post types required for this model', 'jet-engine' ); ?>
				</div>
				<div
					class="jet-ai-builder-card"
					v-for="( cptData, cptSlug ) in jsonModel.postTypes"
					:key="'cpt-' + cptSlug "
				>
					<div class="jet-ai-builder-card__title">
						{{ cptData.title }}:
						<a
							href="#"
							class="jet-ai-builder-card__delete jet-ai-builder-delete-button"
							@click.prevent="deleteCPT( cptSlug )"
						>
							<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.28564 14.1921V3.42857H13.7142V14.1921C13.7142 14.6686 13.5208 15.089 13.1339 15.4534C12.747 15.8178 12.3005 16 11.7946 16H4.20529C3.69934 16 3.25291 15.8178 2.866 15.4534C2.4791 15.089 2.28564 14.6686 2.28564 14.1921Z"></path><path d="M14.8571 1.14286V2.28571H1.14282V1.14286H4.57139L5.56085 0H10.4391L11.4285 1.14286H14.8571Z"></path></svg>
						</a>
					</div>
					<div class="jet-ai-builder-card__content">
						<jet-ai-website-builder-section
							v-if=""
							:parent="cptData.title"
							section-title="<?php _e( 'Supported Core Features', 'jet-engine' ) ?>"
							:data="formatSupports( cptData.supports )"
							items-delimiter=""
							:item-content-callback="( item ) => {
								return '';
							}"
						></jet-ai-website-builder-section>
						<jet-ai-website-builder-section
							v-if="cptData.taxonomies && cptData.taxonomies.length"
							:parent="cptData.title"
							section-title="<?php _e( 'Taxonomies', 'jet-engine' ) ?>"
							:data="cptData.taxonomies"
							items-delimiter=":"
							:item-content-callback="( item ) => {
								return item.name;
							}"
							:on-delete-item="( item ) => {
								deleteTaxonomy( cptSlug, item );
							}"
							:on-edit-item="( item, index ) => {
								editEntity( cptSlug, 'taxonomies', item, index );
							}"
						></jet-ai-website-builder-section>
						<jet-ai-website-builder-section
							v-if="cptData.metaFields && cptData.metaFields.length"
							:parent="cptData.title"
							section-title="<?php _e( 'Meta Fields', 'jet-engine' ) ?>"
							:data="cptData.metaFields"
							items-delimiter=":"
							:item-content-callback="( item ) => {
								return item.name + ', ' + item.type;
							}"
							:visibility-callback="( item ) => {
								return 'relation' !== item.type;
							}"
							:on-delete-item="( item ) => {
								deleteMetaField( cptSlug, item );
							}"
							:on-edit-item="( item, index ) => {
								editEntity( cptSlug, 'metaFields', item, index );
							}"
						></jet-ai-website-builder-section>
						<jet-ai-website-builder-section
							v-if="hasRelations( cptData )"
							:parent="cptData.title"
							section-title="<?php _e( 'Related Entities', 'jet-engine' ); ?>"
							:data="cptRelations( cptData )"
							items-delimiter=":"
							:item-content-callback="( item ) => {
								return item.name;
							}"
						></jet-ai-website-builder-section>
						<div
							class="jet-ai-builder-card__meta"
							v-if="cptSlug === 'product'"
						>
							<div class="jet-ai-builder-card__meta-text">
								<?php
									_e( 'Looks like this is WooCommerce products. If you’re planning to run an online store, it will be better to use the WooCommerce plugin.', 'jet-engine' );
								?>
							</div>
							<div class="jet-ai-builder-card__meta-controls">
								<input
									type="checkbox"
									v-model="cptData.isWoo"
									:id="'is_woo_' + cptSlug"
								>
								<label :for="'is_woo_' + cptSlug"><?php
									_e( 'Use WooCommerce', 'jet-engine' );
								?></label>
							</div>
						</div>
						<div
							class="jet-ai-builder-card__meta"
							v-if="! cptData.withFront && ( ! cptData.taxonomies || ! cptData.taxonomies.length ) && ! cptData.isWoo && ! cptData.customStorage"
						>
							<div class="jet-ai-builder-card__meta-text">
								<?php
									_e( 'This post type doesn`t need separate page on the front-end, so it could be created as CCT. This will help to optimize website performance and reduce DB size.', 'jet-engine' );
								?>
								<a href="https://crocoblock.com/knowledge-base/features/custom-content-type/" target="_blank"><?php
									_e( 'Read more about CCTs', 'jet-engine' );
								?></a>
							</div>
							<div class="jet-ai-builder-card__meta-controls">
								<input
									type="checkbox"
									v-model="cptData.isCCT"
									:id="'cct_' + cptSlug"
								>
								<label :for="'cct_' + cptSlug"><?php
									_e( 'Create as CCT', 'jet-engine' );
								?></label>
							</div>
						</div>
						<div
							class="jet-ai-builder-card__meta"
							v-if="cptData.metaFields && 4 < cptData.metaFields.length && ! cptData.isCCT && ! cptData.isWoo"
						>
							<div class="jet-ai-builder-card__meta-text">
								<?php
									_e( 'Register custom meta storage (beta). This feature will improve performance and reduce DB size. But at the moment it has Beta status.', 'jet-engine' );
								?>
								<a href="https://crocoblock.com/knowledge-base/jetengine/how-to-create-custom-meta-storage-for-cpt/" target="_blank"><?php
									_e( 'Read more about custom meta storage', 'jet-engine' );
								?></a>
							</div>
							<div class="jet-ai-builder-card__meta-controls">
								<input
									type="checkbox"
									v-model="cptData.customStorage"
									:id="'custom_storage_' + cptSlug"
								>
								<label :for="'custom_storage_' + cptSlug"><?php
									_e( 'Register Custom Meta Storage', 'jet-engine' );
								?></label>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="jet-ai-builder-section">
				<h4 class="cx-vui-subtitle"><?php _e( 'Relations:', 'jet-engine' ) ?></h4>
				<div class="jet-ai-builder-note v-indent" v-if="! jsonModel.relations || ! jsonModel.relations.length">
					<?php _e( 'There is no relations required for this model', 'jet-engine' ); ?>
				</div>
				<div
					class="jet-ai-builder-card"
					v-for="( relation, index ) in jsonModel.relations"
					:key="'rel-' + index "
				>
					<div class="jet-ai-builder-card__title">
						{{ relationLabel( relation.from ) }} > {{ relationLabel( relation.to ) }}:
						<a
							href="#"
							class="jet-ai-builder-card__delete jet-ai-builder-delete-button"
							@click.prevent="deleteRelation( relation )"
						>
							<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.28564 14.1921V3.42857H13.7142V14.1921C13.7142 14.6686 13.5208 15.089 13.1339 15.4534C12.747 15.8178 12.3005 16 11.7946 16H4.20529C3.69934 16 3.25291 15.8178 2.866 15.4534C2.4791 15.089 2.28564 14.6686 2.28564 14.1921Z"></path><path d="M14.8571 1.14286V2.28571H1.14282V1.14286H4.57139L5.56085 0H10.4391L11.4285 1.14286H14.8571Z"></path></svg>
						</a>
					</div>
					<div class="jet-ai-builder-card__content">
						<jet-ai-website-builder-section
							:title-callback="() => {
								return '<b>Parent Entity:</b> ' + relationLabel( relation.from );
							}"
						></jet-ai-website-builder-section>
						<jet-ai-website-builder-section
							:title-callback="() => {
								return '<b>Child Entity:</b> ' + relationLabel( relation.to );
							}"
						></jet-ai-website-builder-section>
						<jet-ai-website-builder-section
							:title-callback="() => {
								return '<b>Relation Type:</b> ' + relation.type.replaceAll( '_', ' ' );
							}"
							items-delimiter=""
							:data="[
								{
									title: 'Each ' + relationLabel( relation.from ) + ' item can have ' + relation.type.split( '_' )[2] + ' related ' + relationLabel( relation.to ) + ' item(s)'
								},
								{
									title: 'Each ' + relationLabel( relation.to ) + ' item can have ' + relation.type.split( '_' )[0] + ' related ' + relationLabel( relation.from ) + ' item(s)'
								},
							]"
							:item-content-callback="() => {
								return '';
							}"
						></jet-ai-website-builder-section>
					</div>
				</div>
			</div>
			<div class="jet-ai-builder-section">
				<h4 class="cx-vui-subtitle"><?php _e( 'Filters:', 'jet-engine' ) ?></h4>
				<div class="jet-ai-builder-license-notice" v-if="! hasJSFLicense">
					<?php _e( '<b>Warning:</b> Please obtain a subscription for the JetSmartFilters plugin to use filters functionality.', 'jet-engine' ); ?><br>
					<a href="https://crocoblock.com/plugins/jetsmartfilters/" target="_blank"><?php
						_e( 'Get JetSmartFilters license here' , 'jet-engine' );
					?></a>
				</div>
				<template v-else>
					<div class="jet-ai-builder-note v-indent" v-if="! filtersList || ! filtersList.length">
						<?php _e( 'There is no filters required for this model', 'jet-engine' ); ?>
					</div>
					<div
						class="jet-ai-builder-card"
						v-for="( filter, index ) in filtersList"
						:key="'filter-' + index "
					>
						<div class="jet-ai-builder-card__title">
							{{ filter.title }}:
							<a
								href="#"
								class="jet-ai-builder-card__delete jet-ai-builder-delete-button"
								@click="deleteFilter( filter )"
							>
								<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.28564 14.1921V3.42857H13.7142V14.1921C13.7142 14.6686 13.5208 15.089 13.1339 15.4534C12.747 15.8178 12.3005 16 11.7946 16H4.20529C3.69934 16 3.25291 15.8178 2.866 15.4534C2.4791 15.089 2.28564 14.6686 2.28564 14.1921Z"></path><path d="M14.8571 1.14286V2.28571H1.14282V1.14286H4.57139L5.56085 0H10.4391L11.4285 1.14286H14.8571Z"></path></svg>
							</a>
						</div>
						<div class="jet-ai-builder-card__content">
							<jet-ai-website-builder-section
								:title-callback="() => {
									return '<b>Filter Type:</b> ' + filter.filter_type;
								}"
							></jet-ai-website-builder-section>
							<jet-ai-website-builder-section
								:title-callback="() => {
									return '<b>Filter Query By:</b> ' + filter.query_type;
								}"
							></jet-ai-website-builder-section>
							<jet-ai-website-builder-section
								:title-callback="() => {
									return '<b>Query Var:</b> ' + filter.query_var;
								}"
							></jet-ai-website-builder-section>
						</div>
					</div>
				</template>
			</div>
		</div>
		<div
			class="jet-ai-builder-actions jet-ai-create-model"
			v-if="jsonModel && jsonModel.postTypes"
		>
			<cx-vui-button
				button-style="accent"
				@click="createModel()"
				:loading="isCreatingModel"
				:disabled="! confirmCreation"
			>
				<span
					slot="label"
				><?php
					_e( 'Create Website Model', 'jet-engine' );
				?></span>
			</cx-vui-button>
			<label class="jet-ai-builder-confirm">
				<input type="checkbox" v-model="confirmCreation">
				<?php _e( 'I understand that by clicking <b>"Create Website Model"</b> all described entities will be physically created on my website', 'jet-engine' ); ?>
			</label>
		</div>
		<div
			v-if="createError"
			class="cx-vui-inline-notice cx-vui-inline-notice--error jet-ai-builder-create-error"
		>{{ createError }}</div>
	</div>
</div>
