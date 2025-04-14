<div class="jet-ai-builder-card__section">
	<div class="jet-ai-builder-card__section-title">
		<span v-if="titleCallback" v-html="titleCallback()"></span>
		<span v-else><b>{{ sectionTitle }}</b> of {{ parent }}:</span>
		<button
			v-if="data && data.length"
			type="button"
			class="jet-ai-builder-card__section-trigger button button-secondary"
			@click="expanded = ! expanded"
		>
			<span v-if="! expanded">+</span>
			<span v-if="expanded">-</span>
		</button>
		<a
			href="#"
			v-if="canDeleteSection"
			class="jet-ai-builder-card__section-delete jet-ai-builder-delete-button"
		>
			<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.28564 14.1921V3.42857H13.7142V14.1921C13.7142 14.6686 13.5208 15.089 13.1339 15.4534C12.747 15.8178 12.3005 16 11.7946 16H4.20529C3.69934 16 3.25291 15.8178 2.866 15.4534C2.4791 15.089 2.28564 14.6686 2.28564 14.1921Z"></path><path d="M14.8571 1.14286V2.28571H1.14282V1.14286H4.57139L5.56085 0H10.4391L11.4285 1.14286H14.8571Z"></path></svg>
		</a>
	</div>
	<div
		class="jet-ai-builder-card__section-items"
		v-if="expanded && data && data.length"
	>
		<div
			class="jet-ai-builder-card__section-item"
			v-for="( item, index ) in data"
			v-if="showItem( item )"
		>
			<i v-if="! isEditedItem( index )">{{ item.title }}{{ itemsDelimiter }}</i>
			<code v-if="! isEditedItem( index )">{{ itemContentCallback( item ) }}</code>
			<div v-if="isEditedItem( index )" class="jet-ai-builder-card__section-edit-controls">
				<input
					v-if="item.title"
					type="text"
					class="jet-ai-builder-card__section-edit-input"
					v-model="newItemData.title"
				>
				<input
					v-if="item.name"
					type="text"
					class="jet-ai-builder-card__section-edit-input"
					v-model="newItemData.name"
				>
			</div>
			<a
				href="#"
				v-if="onEditItem && ! isEditedItem( index )"
				class="jet-ai-builder-card__section-item-edit jet-ai-builder-edit-button"
				@click.prevent="editItem( item, index )"
			>
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><rect x="0" fill="none" width="20" height="20"/><g><path d="M13.89 3.39l2.71 2.72c.46.46.42 1.24.03 1.64l-8.01 8.02-5.56 1.16 1.16-5.58s7.6-7.63 7.99-8.03c.39-.39 1.22-.39 1.68.07zm-2.73 2.79l-5.59 5.61 1.11 1.11 5.54-5.65zm-2.97 8.23l5.58-5.6-1.07-1.08-5.59 5.6z"/></g></svg>
			</a>
			<a
				href="#"
				v-if="onDeleteItem && ! isEditedItem( index )"
				class="jet-ai-builder-card__section-item-delete jet-ai-builder-delete-button"
				@click.prevent="onDeleteItem( item )"
			>
				<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.28564 14.1921V3.42857H13.7142V14.1921C13.7142 14.6686 13.5208 15.089 13.1339 15.4534C12.747 15.8178 12.3005 16 11.7946 16H4.20529C3.69934 16 3.25291 15.8178 2.866 15.4534C2.4791 15.089 2.28564 14.6686 2.28564 14.1921Z"></path><path d="M14.8571 1.14286V2.28571H1.14282V1.14286H4.57139L5.56085 0H10.4391L11.4285 1.14286H14.8571Z"></path></svg>
			</a>
			<a
				href="#"
				v-if="onEditItem && isEditedItem( index )"
				class="jet-ai-builder-card__section-item-edit jet-ai-builder-edit-button jet-ai-edit-confirm"
				@click.prevent="confirmEdit( item, index )"
			>
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><rect x="0" fill="none" width="20" height="20"/><g><path d="M14.83 4.89l1.34.94-5.81 8.38H9.02L5.78 9.67l1.34-1.25 2.57 2.4z"/></g></svg>
			</a>
			<a
				href="#"
				v-if="onEditItem && isEditedItem( index )"
				class="jet-ai-builder-card__section-item-delete jet-ai-builder-delete-button jet-ai-edit-cancel"
				@click.prevent="cancelEdit( index )"
			>
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><rect x="0" fill="none" width="20" height="20"/><g><path d="M14.95 6.46L11.41 10l3.54 3.54-1.41 1.41L10 11.42l-3.53 3.53-1.42-1.42L8.58 10 5.05 6.47l1.42-1.42L10 8.58l3.54-3.53z"/></g></svg>
			</a>
		</div>
	</div>
</div>
