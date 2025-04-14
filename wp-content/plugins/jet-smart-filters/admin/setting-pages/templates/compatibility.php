<div
	class="jet-smart-filters-settings-page jet-smart-filters-settings-page__compatibility"
>
	<cx-vui-collapse
		collapsed="true"
	>
		<h3 class="cx-vui-subtitle" slot="title"><?php _e( 'WooCommerce', 'jet-smart-filters' ); ?></h3>
		<div class="cx-vui-panel" slot="content">
			<cx-vui-switcher
				label="<?php _e( 'Hide Out-of-Stock Variations in Filter Results', 'jet-smart-filters' ); ?>"
				:wrapper-css="[ 'equalwidth' ]"
				v-model="settings.wc_hide_out_of_stock_variations"
			></cx-vui-switcher>
		</div>
	</cx-vui-collapse>
</div>
