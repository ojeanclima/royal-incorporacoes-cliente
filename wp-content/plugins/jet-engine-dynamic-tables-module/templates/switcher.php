<div class="jet-table-switcher">
	<div class="jet-table-switcher__state">
		<div
			class="jet-table-switcher__state-item"
			:class="{ 'jet-table-switcher__state-item--active': 's' === state }"
			@click="state = 's'"
		>{{ sLabel }}</div>
		<div
			class="jet-table-switcher__state-item"
			:class="{ 'jet-table-switcher__state-item--active': 'e' === state }"
			@click="state = 'e'"
		>{{ eLabel }}</div>
	</div>
	<div class="jet-table-switcher__content">
		<slot name="state-s" v-if="'s' === state"></slot>
		<slot name="state-e" v-if="'e' === state"></slot>
	</div>
</div>
