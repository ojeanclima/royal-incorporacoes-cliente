<div class="cx-vui-panel jet-ai-builder-models-page">
	<div v-if="modelUID" class="jet-ai-builder-model" :id="'models/' + modelUID">
		<div class="jet-ai-builder-model__name">
			<input
				type="text"
				v-model="modelName"
				:disabled="isUpdatingModel"
				class="jet-ai-builder-model__name-input"
				placeholder="<?php _e( 'Model Name', 'jet-engine' ); ?>"
			>
			<cx-vui-button
				button-style="accent"
				@click="updateModelName()"
				size="mini"
				v-if="modelName"
				:loading="isUpdatingModel"
			>
				<span
					slot="label"
				><?php
					_e( 'Update', 'jet-engine' );
				?></span>
			</cx-vui-button>
		</div>
		<div v-if="modelHTML" v-html="modelHTML" class="jet-ai-builder-model__data"></div>
		<div class="jet-ai-builder-model__info" v-if="modelInfo.topic || modelInfo.functionality">
			<div
				class="jet-ai-builder-model__info-key"
				v-if="modelInfo.topic"
			><?php
				esc_html_e( 'Model Topic:', 'jet-engine' );
			?></div>
			<div
				class="jet-ai-builder-model__info-value"
				v-if="modelInfo.topic"
			>{{ modelInfo.topic }}</div>
			<div
				class="jet-ai-builder-model__info-key"
				v-if="modelInfo.functionality"
			><?php
				esc_html_e( 'Requested Functionality:', 'jet-engine' );
			?></div>
			<div
				class="jet-ai-builder-model__info-value"
				v-if="modelInfo.functionality"
			>{{ modelInfo.functionality }}</div>
		</div>
	</div>
	<div v-else-if="models.length" class="jet-ai-builder-models" id="models">
		<div class="jet-ai-builder-models__item" v-for="modelData in models">
			<a :href="getModelURL( modelData.uid )" @click="loadModelHTML( modelData.uid )">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
					<g><path d="M12 2l4 4v12H4V2h8zM5 3v1h6V3H5zm7 3h3l-3-3v3zM5 5v1h6V5H5zm10 3V7H5v1h10zm0 2V9H5v1h10zm0 2v-1H5v1h10zm-4 2v-1H5v1h6z"/></g>
				</svg>
				{{ modelData.name }}
			</a>
			<button type="button" class="jet-ai-builder-models__item-remove" @click="deleteModelData = modelData">
				<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.28564 14.1921V3.42857H13.7142V14.1921C13.7142 14.6686 13.5208 15.089 13.1339 15.4534C12.747 15.8178 12.3005 16 11.7946 16H4.20529C3.69934 16 3.25291 15.8178 2.866 15.4534C2.4791 15.089 2.28564 14.6686 2.28564 14.1921Z"></path><path d="M14.8571 1.14286V2.28571H1.14282V1.14286H4.57139L5.56085 0H10.4391L11.4285 1.14286H14.8571Z"></path></svg>
			</button>
		</div>
		<dialog :open="false !== deleteModelData">
			<p>Are you sure you want to delete <b>{{ deleteModelData.name }}</b>?</p>
			<p><b>Please note:</b> this action will remove only inforamtion about the model from the current page. All entitites created by the model will be kept.</p>
			<button class="jet-ai-builder-models__delete-confirm" @click="deleteModel()">{{ deleteModelLabel }}</button>
			<button class="jet-ai-builder-models__delete-cancel" @click="deleteModelData = false">Cancel</button>
		</dialog>
	</div>
	<div v-else class="jet-ai-builder-no-models">
		<?php _e( 'You not created any models yet.', 'jet-engine' ) ?><br>
		<a :href="window.JetEngineWebsiteBuilderData.base_url"><?php _e( 'Create a first one', 'jet-engine' ); ?></a>
	</div>
</div>
