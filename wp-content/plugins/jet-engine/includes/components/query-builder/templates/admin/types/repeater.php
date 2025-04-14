<?php
/**
 * Posts query component template
 */
?>
<div class="jet-engine-edit-page__fields">
	<div class="cx-vui-collapse__heading">
		<h3 class="cx-vui-subtitle"><?php _e( 'Repeater Query', 'jet-engine' ); ?></h3>
	</div>
	<div class="cx-vui-panel">
		<cx-vui-select
			label="<?php _e( 'Source', 'jet-engine' ); ?>"
			description="<?php _e( 'Repeater field source', 'jet-engine' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:options-list="sourcesList"
			size="fullwidth"
			name="repeater_source"
			v-model="query.source"
		></cx-vui-select>
		<cx-vui-select
			label="<?php _e( 'JetEngine Field', 'jet-engine' ); ?>"
			description="<?php _e( 'Enter JetEngine meta field name to use as items source', 'jet-engine' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:groups-list="metaFields"
			size="fullwidth"
			key="jet_engine_field"
			name="jet_engine_field"
			v-if="'jet_engine' === query.source"
			v-model="query.jet_engine_field"
		></cx-vui-select>
		<cx-vui-select
			label="<?php _e( 'JetEngine Option', 'jet-engine' ); ?>"
			description="<?php _e( 'Select JetEngine option name to use as items source', 'jet-engine' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:groups-list="optionsFields"
			size="fullwidth"
			name="jet_engine_option_field"
			key="jet_engine_option_field"
			v-if="'jet_engine_option' === query.source"
			v-model="query.jet_engine_option_field"
		></cx-vui-select>
		<cx-vui-input
			label="<?php _e( 'Repeater Field Name', 'jet-engine' ); ?>"
			description="<?php _e( 'Enter any custom meta field name to use as items source', 'jet-engine' ); ?>"
			:wrapper-css="[ 'equalwidth', 'has-macros' ]"
			size="fullwidth"
			name="custom_field"
			v-model="query.custom_field"
			v-if="'custom' === query.source"
		><jet-query-dynamic-args v-model="dynamicQuery.custom_field"></jet-query-dynamic-args></cx-vui-input>
		<?php do_action( 'jet-engine/query-builder/repeater/controls' ); ?>
		<cx-vui-input
			label="<?php _e( 'Object ID', 'jet-engine' ); ?>"
			description="<?php _e( 'Set object ID to get meta data from. Leave empty to use current object ID', 'jet-engine' ); ?>"
			:wrapper-css="[ 'equalwidth', 'has-macros' ]"
			size="fullwidth"
			name="object_id"
			v-model="query.object_id"
			v-if="'jet_engine_option' !== query.source"
		><jet-query-dynamic-args v-model="dynamicQuery.object_id"></jet-query-dynamic-args></cx-vui-input>
		<cx-vui-component-wrapper
			:wrapper-css="[ 'fullwidth-control' ]"
		>
			<div class="cx-vui-inner-panel query-panel">
				<div class="cx-vui-component__label"><?php _e( 'Order & Order By', 'jet-engine' ); ?></div>
				<cx-vui-repeater
					button-label="<?php _e( 'Add new sorting parameter', 'jet-engine' ); ?>"
					button-style="accent"
					button-size="mini"
					v-model="query.orderby"
					@add-new-item="addNewField( $event, [ { prop: 'orderby', value: 'index' }, { prop: 'order_type', value: 'numeric' }, { prop: 'order', value: 'ASC' } ], query.orderby )"
				>
					<cx-vui-repeater-item
						v-for="( order, index ) in query.orderby"
						:title="query.orderby[ index ].orderby"
						:subtitle="query.orderby[ index ].order"
						:collapsed="isCollapsed( order )"
						:index="index"
						@clone-item="cloneField( $event, order._id, query.orderby )"
						@delete-item="deleteField( $event, order._id, query.orderby )"
						:key="order._id"
					>
						<cx-vui-select
							label="<?php _e( 'Order By', 'jet-engine' ); ?>"
							description="<?php _e( 'Sort retrieved posts by selected parameter.', 'jet-engine' ); ?>"
							:wrapper-css="[ 'equalwidth' ]"
							:options-list="[
								{
									value: 'index',
									label: 'Repeater Index',
								},
								{
									value: 'field',
									label: 'Repeater Field',
								},
							]"
							size="fullwidth"
							:value="query.orderby[ index ].orderby"
							@input="setFieldProp( order._id, 'orderby', $event, query.orderby )"
						></cx-vui-select>
						<cx-vui-input
							label="<?php _e( 'Field Name/ID', 'jet-engine' ); ?>"
							description="<?php _e( 'Repeater field name to order by. Please use name of the field, not the visual label.', 'jet-engine' ); ?>"
							v-if="'field' === query.orderby[ index ].orderby"
							:wrapper-css="[ 'equalwidth' ]"
							size="fullwidth"
							:value="query.orderby[ index ].field_name"
							@input="setFieldProp( order._id, 'field_name', $event, query.orderby )"
						></cx-vui-input>
						<cx-vui-select
							v-if="'field' === query.orderby[ index ].orderby"
							label="<?php _e( 'Ordering Type', 'jet-engine' ); ?>"
							description="<?php _e( 'Depending on the type of the data stored in the field, select apropriate ordering type.', 'jet-engine' ); ?>"
							:wrapper-css="[ 'equalwidth' ]"
							:options-list="[
								{
									value: 'numeric',
									label: 'Numeric (1, 2, 3)',
								},
								{
									value: 'alphabetical',
									label: 'Alphabetical (a, b, c)',
								},
								{
									value: 'dates',
									label: 'Dates',
								},
							]"
							size="fullwidth"
							:value="query.orderby[ index ].order_type"
							@input="setFieldProp( order._id, 'order_type', $event, query.orderby )"
						></cx-vui-select>
						<cx-vui-select
							label="<?php _e( 'Order', 'jet-engine' ); ?>"
							description="<?php _e( 'Designates the ascending or descending order of the `Order By` parameter', 'jet-engine' ); ?>"
							:wrapper-css="[ 'equalwidth' ]"
							:options-list="[
								{
									value: 'ASC',
									label: 'From lowest to highest values (1, 2, 3; a, b, c)',
								},
								{
									value: 'DESC',
									label: 'From highest to lowest values (3, 2, 1; c, b, a)',
								},
							]"
							size="fullwidth"
							:value="query.orderby[ index ].order"
							@input="setFieldProp( order._id, 'order', $event, query.orderby )"
						></cx-vui-select>
					</cx-vui-repeater-item>
				</cx-vui-repeater>
			</div>
		</cx-vui-component-wrapper>
		<cx-vui-component-wrapper
			:wrapper-css="[ 'fullwidth-control' ]"
		>
			<div class="cx-vui-inner-panel query-panel">
				<div class="cx-vui-component__label"><?php _e( 'Query Arguments', 'jet-engine' ); ?></div>
				<div class="cx-vui-component__desc"><?php _e( 'If you want to select only specific items from repeater field, set appropriate query arguments here', 'jet-engine' ); ?></div>
				<cx-vui-repeater
					button-label="<?php _e( 'Add new', 'jet-engine' ); ?>"
					button-style="accent"
					button-size="mini"
					v-model="query.meta_query"
					@add-new-item="addNewField( $event, [], query.meta_query, newDynamicMeta )"
				>
					<cx-vui-repeater-item
						v-for="( clause, index ) in query.meta_query"
						:collapsed="isCollapsed( clause )"
						:index="index"
						@clone-item="cloneField( $event, clause._id, query.meta_query, newDynamicMeta )"
						@delete-item="deleteField( $event, clause._id, query.meta_query, deleteDynamicMeta )"
						:key="clause._id"
					>
						<cx-vui-input
							label="<?php _e( 'Field key/name', 'jet-engine' ); ?>"
							description="<?php _e( 'Enter sub-field of main repeater field to query by', 'jet-engine' ); ?>"
							:wrapper-css="[ 'equalwidth', 'has-macros' ]"
							size="fullwidth"
							:value="query.meta_query[ index ].key"
							@input="setFieldProp( clause._id, 'key', $event, query.meta_query )"
						><jet-query-dynamic-args v-model="dynamicQuery.meta_query[ clause._id ].key"></jet-query-dynamic-args></cx-vui-input>
						<cx-vui-select
							label="<?php _e( 'Compare', 'jet-engine' ); ?>"
							description="<?php _e( 'Operator to test', 'jet-engine' ); ?>"
							:wrapper-css="[ 'equalwidth' ]"
							:options-list="operators"
							size="fullwidth"
							:value="query.meta_query[ index ].compare"
							@input="setFieldProp( clause._id, 'compare', $event, query.meta_query )"
						></cx-vui-select>
						<cx-vui-input
							label="<?php _e( 'Value', 'jet-engine' ); ?>"
							:wrapper-css="[ 'equalwidth', 'has-macros' ]"
							size="fullwidth"
							:value="query.meta_query[ index ].value"
							@input="setFieldProp( clause._id, 'value', $event, query.meta_query )"
						><jet-query-dynamic-args v-model="dynamicQuery.meta_query[ clause._id ].value"></jet-query-dynamic-args></cx-vui-input>
						<cx-vui-select
							label="<?php _e( 'Type', 'jet-engine' ); ?>"
							description="<?php _e( 'Data type stored in the given field', 'jet-engine' ); ?>"
							:wrapper-css="[ 'equalwidth' ]"
							:options-list="dataTypes"
							size="fullwidth"
							:value="query.meta_query[ index ].type"
							@input="setFieldProp( clause._id, 'type', $event, query.meta_query )"
						></cx-vui-select>
					</cx-vui-repeater-item>
				</cx-vui-repeater>
			</div>
		</cx-vui-component-wrapper>
		<cx-vui-select
			v-if="1 < query.meta_query.length"
			label="<?php _e( 'Relation', 'jet-engine' ); ?>"
			description="<?php _e( 'The logical relationship between meta query clauses', 'jet-engine' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:options-list="[
				{
					value: 'and',
					label: '<?php _e( 'And', 'jet-engine' ); ?>',
				},
				{
					value: 'or',
					label: '<?php _e( 'Or', 'jet-engine' ); ?>',
				},
			]"
			size="fullwidth"
			v-model="query.meta_query_relation"
		></cx-vui-select>
		<cx-vui-textarea
			label="<?php _e( 'Fields List', 'jet-engine' ); ?>"
			description="<?php _e( 'Comma-separated repeater fields list to use in Dynamic Field, Dynamic Tags or JetEngine data shortcode', 'jet-engine' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			size="fullwidth"
			name="object_id"
			v-model="query.fields_list"
			v-if="'custom' === query.source"
		></cx-vui-textarea>
		<cx-vui-input
			label="<?php _e( 'Show/Per Page Limit', 'jet-engine' ); ?>"
			description="<?php _e( 'If using with JetSmartFilters pagination - its number of returned items per page. If without pagination - its number of visible items in the listing grid.', 'jet-engine' ); ?>"
			:wrapper-css="[ 'equalwidth', 'has-macros' ]"
			size="fullwidth"
			v-model="query.per_page"
		><jet-query-dynamic-args v-model="dynamicQuery.limit_per_page"></jet-query-dynamic-args></cx-vui-input>
		<cx-vui-input
			label="<?php _e( 'Offset', 'jet-engine' ); ?>"
			description="<?php _e( 'Number of items to skip from start.', 'jet-engine' ); ?>"
			:wrapper-css="[ 'equalwidth', 'has-macros' ]"
			size="fullwidth"
			v-model="query.offset"
		><jet-query-dynamic-args v-model="dynamicQuery.offset"></jet-query-dynamic-args></cx-vui-input>
		<cx-vui-switcher
			label="<?php _e( 'Use Preview Settings for Listing Item', 'jet-engine' ); ?>"
			description="<?php _e( 'If checked, the same post and query string will be used for Listing Item preview based on this query.', 'jet-engine' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			name="use_preview_settings"
			v-model="query.use_preview_settings"
		></cx-vui-switcher>
	</div>
</div>
