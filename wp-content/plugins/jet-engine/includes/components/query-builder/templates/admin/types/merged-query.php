<?php
/**
 * Posts query component template
 */
?>
<div class="jet-engine-edit-page__fields">
	<div class="cx-vui-collapse__heading">
		<h3 class="cx-vui-subtitle"><?php _e( 'Merged Query', 'jet-engine' ); ?></h3>
	</div>
	<div class="cx-vui-panel">
		<div style="padding: 20px;">
			<div style="padding-bottom: 10px;">
				<?php _e( 'With this query type, you can merge the results of several <b>different queries</b> of the <b>same type</b> into a single query. It can be useful when, for example, you want to combine posts based on different criteria into a single listing grid.', 'jet-engine' ); ?>
			</div>
			<div style="font-size: 1.1em;">
				<?php _e( '<b>Please, note!</b> Merged query may produce unexpected results in complicated cases with load more, pagination, filtering etc.', 'jet-engine' ); ?>
			</div>
		</div>
		
		<cx-vui-select
			label="<?php _e( 'Query Type', 'jet-engine' ); ?>"
			description="<?php _e( 'Merge queries of type', 'jet-engine' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:options-list="queryTypes"
			size="fullwidth"
			v-model="query.base_query_type"
			@on-input="resetQueries"
		></cx-vui-select>
		<cx-vui-switcher
			label="<?php _e( 'Exclude Duplicated Items', 'jet-engine' ); ?>"
			description="<?php _e( 'Exclude from next queries items, which already was found.', 'jet-engine' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			v-model="query.exclude_duplicates"
		></cx-vui-switcher>
		<cx-vui-input
			label="<?php _e( 'Max items per page', 'jet-engine' ); ?>"
			description="<?php _e( 'Set maximum items number to display per merged query page. Keep empty if you want to inherit items per page number from each children query.', 'jet-engine' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			size="fullwidth"
			v-model="query.max_items_per_page"
		></cx-vui-input>
		<cx-vui-component-wrapper
			:wrapper-css="[ 'query-fullwidth' ]"
			style="padding: 0; border-top: 1px solid #ECECEC;"
		>
			<div class="cx-vui-inner-panel query-panel">
				<div class="cx-vui-component__label"><?php _e( 'Queries to Merge', 'jet-engine' ); ?></div>
				<div class="cx-vui-component__description" style="padding-bottom: 15px;"><?php _e( 'Select the specific queries you want to merge into a single query', 'jet-engine' ); ?></div>
				<cx-vui-repeater
					button-label="<?php _e( 'Add new query', 'jet-engine' ); ?>"
					button-style="accent"
					button-size="mini"
					v-model="query.queries"
					@add-new-item="addNewField( $event, [], query.queries )"
				>
					<cx-vui-repeater-item
						v-for="( mergeQuery, index ) in query.queries"
						:title="queryName( query.queries[ index ].query_id )"
						:collapsed="isCollapsed( mergeQuery )"
						:index="index"
						@clone-item="cloneField( $event, mergeQuery._id, query.queries )"
						@delete-item="deleteField( $event, mergeQuery._id, query.queries )"
						:key="mergeQuery._id"
					>
						<cx-vui-select
							label="<?php _e( 'Query', 'jet-engine' ); ?>"
							description="<?php _e( 'Only queries of the selected type are allowed here', 'jet-engine' ); ?>"
							:wrapper-css="[ 'equalwidth' ]"
							:options-list="allowedQueries( query.queries[ index ].query_id )"
							size="fullwidth"
							:value="query.queries[ index ].query_id"
							@input="setFieldProp( mergeQuery._id, 'query_id', $event, query.queries )"
						></cx-vui-select>
					</cx-vui-repeater-item>
				</cx-vui-repeater>
			</div>
		</cx-vui-component-wrapper>
	</div>
</div>