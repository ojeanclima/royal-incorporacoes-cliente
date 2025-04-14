<div class="jet-engine-navigation">
	<div class="jet-engine-navigation__count">
		<?php printf(
			__( 'Showing %1$s - %2$s of %3$s results', 'jet-engine' ),
			'{{ startItemIndex }}',
			'{{ endItemIndex }}',
			'{{ totalItems }}',
		); ?>
	</div>

	<cx-vui-pagination
		v-if="perPage < totalItems"
		:total="totalItems"
		:page-size="perPage"
		:current="currentPage"
		@on-change="changePage"
	></cx-vui-pagination>

	<cx-vui-input
		:label="'<?php _e( 'Per page', 'jet-engine' ); ?>'"
		class="jet-engine-navigation__per-page"
		type="number"
		:placeholder="String( initialPerPage )"
		:min="Number(1)"
		:max="Number(100)"
		:step="Number(1)"
		:value="perPage"
		@input="changePerPage"
	></cx-vui-input>
</div>
