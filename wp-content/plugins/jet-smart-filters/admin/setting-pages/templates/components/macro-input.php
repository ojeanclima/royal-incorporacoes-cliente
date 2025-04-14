<div class="jsf_macro-input"
	 :class="{'jsf_macro-input-dropdown-opened': dropdownOpened}"
>
	<div v-if="label"
		 class="jsf_macro-input-label">
		{{ label }}
	</div>
	<div class="jsf_macro-input-textarea">
		<textarea
			:value="value"
			:placeholder="placeholder"
			@input="updateValue($event.target.value)"
			ref="inputField"
		></textarea>
		<div class="jsf_macro-input-dropdown-button"
			 :class="{ 'jsf_disabled': !optionsList.length }"
			 @click="onDropdownButtonClick"
		>
			<svg v-if="dropdownOpened" viewBox="0 0 20 20" width="24" height="24"><rect x="0" fill="none" width="20" height="20"></rect><g><path d="M14.95 6.46L11.41 10l3.54 3.54-1.41 1.41L10 11.42l-3.53 3.53-1.42-1.42L8.58 10 5.05 6.47l1.42-1.42L10 8.58l3.54-3.53z"></path></g></svg>
			<svg v-else viewBox="0 0 20 20" width="24" height="24"><rect x="0" fill="none" width="20" height="20"></rect><g><path d="M14 10c2.2 0 4-1.8 4-4s-1.8-4-4-4-4 1.8-4 4 1.8 4 4 4zm-1-5V3h2v2h2v2h-2v2h-2V7h-2V5h2zM9 6c0-1.6.8-3 2-4h-1c-3.9 0-7 .9-7 2 0 1 2.6 1.8 6 2zm1 9c-3.9 0-7-.9-7-2v3c0 1.1 3.1 2 7 2s7-.9 7-2v-3c0 1.1-3.1 2-7 2zm2.8-4.2c-.9.1-1.9.2-2.8.2-3.9 0-7-.9-7-2v3c0 1.1 3.1 2 7 2s7-.9 7-2v-2c-.9.7-1.9 1-3 1-.4 0-.8-.1-1.2-.2zM10 10h1c-1-.7-1.7-1.8-1.9-3C5.7 6.9 3 6 3 5v3c0 1.1 3.1 2 7 2z"></path></g></svg>
		</div>
	</div>
	<div class="jsf_macro-input-dropdown-body">
		<div v-if="suboptions"
			 class="jsf_macro-input-suboptions"
		>
			<div v-if="suboptions.label"
				 class="jsf_macro-input-suboptions-title"
			>
				{{ suboptions.label }}
			</div>
			<component v-if="suboptions.controls"
					   v-for="(controlData, index) in suboptions.controls"
					   v-bind="controlData"
					   @input="onSuboptionsChange($event, index)"
			/>
			<div class="jsf_macro-input-suboptions-actions">
				<button type="button"
						class="jsf_macro-input-suboptions-actions-back"
						@click="onSuboptionsBackClick"
				>
					<?php _e( 'Back', 'jet-smart-filters' ); ?>
				</button>
				<button type="button"
						class="jsf_macro-input-suboptions-actions-add"
						@click="onSuboptionsAddClick"
				>
					<?php _e( 'Add', 'jet-smart-filters' ); ?>
				</button>
			</div>
		</div>
		<template v-else>
			<ul v-if="optionsList.length"
				class="jsf_macro-input-options"
			>
				<li v-for="( option, index ) in optionsList"
					:key="option.value"
					class="jsf_macro-input-option"
				>
					<div class="jsf_macro-input-option-button"
						@click="onOptionClick(option)"
					>
						<span class="jsf_macro-input-option-button-mark">≫</span>
						{{ option.label }}
					</div>
				</li>
			</ul>
		</template>
	</div>
</div>