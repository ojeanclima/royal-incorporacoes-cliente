<cx-vui-repeater
	button-label="<?php _e( 'New column', 'jet-engine' ); ?>"
	button-style="accent"
	button-size="mini"
	v-model="columnsList"
	@input="onInput"
	@add-new-item="addNewField( $event, [], columnsList )"
>
	<cx-vui-repeater-item
		v-for="( column, index ) in columnsList"
		:title="columnsList[ index ].name"
		:collapsed="isCollapsed( column )"
		:index="index"
		@clone-item="cloneField( $event, column._id, columnsList )"
		@delete-item="deleteField( $event, column._id, columnsList )"
		:key="column._id"
	>
		<cx-vui-input
			label="<?php _e( 'Column Name', 'jet-engine' ); ?>"
			description="<?php _e( 'Column name is displayed in the table heading.', 'jet-engine' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			size="fullwidth"
			:value="columnsList[ index ].name"
			@input="setFieldProp( column._id, 'name', $event, columnsList )"
		></cx-vui-input>
		<cx-vui-select
			label="<?php _e( 'Column Content', 'jet-engine' ); ?>"
			description="<?php _e( 'Type of content displayed in the column', 'jet-engine' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:options-list="contentTypes"
			size="fullwidth"
			:value="columnsList[ index ].content"
			@input="setFieldProp( column._id, 'content', $event, columnsList )"
		></cx-vui-select>
		<cx-vui-select
			label="<?php _e( 'Data Source', 'jet-engine' ); ?>"
			description="<?php _e( 'Select data source for current column', 'jet-engine' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:options-list="allowedDataSources()"
			size="fullwidth"
			v-if="column.content && 'template' !== column.content"
			:value="columnsList[ index ].data_source"
			@input="setFieldProp( column._id, 'data_source', $event, columnsList )"
		></cx-vui-select>
		<cx-vui-select
			label="<?php _e( 'Select Field', 'jet-engine' ); ?>"
			description="<?php _e( 'Select field from the current object to show in this column', 'jet-engine' ); ?>"
			:groups-list="objects"
			:wrapper-css="[ 'equalwidth' ]"
			size="fullwidth"
			v-if="column.content && 'template' !== column.content && 'object' === column.data_source"
			:value="columnsList[ index ].object_field"
			@input="setFieldProp( column._id, 'object_field', $event, columnsList )"
			:key="column._id + '_field'"
		></cx-vui-select>
		<cx-vui-select
			label="<?php _e( 'Select Column', 'jet-engine' ); ?>"
			description="<?php _e( 'Select column from previously fetched columns. Press Re-fetch button to update columns list.', 'jet-engine' ); ?>"
			:options-list="allowedColumnsForOptions()"
			:wrapper-css="[ 'equalwidth' ]"
			size="fullwidth"
			v-if="column.content && 'template' !== column.content && 'fetched' === column.data_source"
			:value="columnsList[ index ].fetched_column"
			@input="setFieldProp( column._id, 'fetched_column', $event, columnsList )"
			:key="column._id + '_columns'"
		></cx-vui-select>
		<cx-vui-select
			label="<?php _e( 'Select Field', 'jet-engine' ); ?>"
			description="<?php _e( 'Select meta field from JetEngine fields to show in this column', 'jet-engine' ); ?>"
			:groups-list="fieldsList"
			:wrapper-css="[ 'equalwidth' ]"
			size="fullwidth"
			placeholder="<?php _e( 'Select...', 'jet-engine' ); ?>"
			v-if="column.content && 'template' !== column.content && 'meta' === column.data_source"
			:value="columnsList[ index ].meta_key"
			@input="setFieldProp( column._id, 'meta_key', $event, columnsList )"
			:key="column._id + '_meta_key'"
		></cx-vui-select>
		<cx-vui-input
			label="<?php _e( 'Custom Field Key', 'jet-engine' ); ?>"
			description="<?php _e( 'Or set any custom field key to get data from', 'jet-engine' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			size="fullwidth"
			v-if="column.content && 'template' !== column.content && 'meta' === column.data_source"
			:value="columnsList[ index ].custom_meta_key"
			@input="setFieldProp( column._id, 'custom_meta_key', $event, columnsList )"
		></cx-vui-input>
		<cx-vui-switcher
			label="<?php _e( 'Filter Column Output', 'jet-engine' ); ?>"
			description="<?php _e( 'Apply filtering function to the column value to change display format etc.', 'jet-engine' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			v-if="column.content && 'template' !== column.content"
			:value="columnsList[ index ].apply_callback"
			@input="setFieldProp( column._id, 'apply_callback', $event, columnsList )"
		></cx-vui-switcher>
		<cx-vui-select
			label="<?php _e( 'Filter Callback', 'jet-engine' ); ?>"
			description="<?php _e( 'Select callback function to filter the column value', 'jet-engine' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:options-list="callbacks"
			size="fullwidth"
			v-if="column.apply_callback && column.content && 'template' !== column.content"
			:value="columnsList[ index ].filter_callback"
			@input="setFieldProp( column._id, 'filter_callback', $event, columnsList )"
		></cx-vui-select>
		<component
			v-for="control in callbacksArgs"
			v-if="column.apply_callback && column.content && 'template' !== column.content && control.if.includes( column.filter_callback )"
			:is="control.type"
			:options-list="control.options"
			:label="control.label"
			:description="control.description"
			:wrapper-css="[ 'equalwidth' ]"
			size="fullwidth"
			:value="columnsList[ index ][ control.id ]"
			@input="setFieldProp( column._id, control.id, $event, columnsList )"
			:key="'callback__' + control.id"
		/>
		<cx-vui-component-wrapper
			label="<?php _e( 'Note!', 'jet-engine' ); ?>"
			description="<?php _e( 'Due to implementation logic of the current callback, we recommend to use it inside Elementor template and then use this template for column.', 'jet-engine' ); ?>"
			v-if="columnsList[ index ].apply_callback && [ 'jet_engine_img_gallery_grid', 'jet_engine_img_gallery_slider' ].includes( columnsList[ index ].filter_callback )"
			:wrapper-css="[ 'fullwidth' ]"
		></cx-vui-component-wrapper>
		<cx-vui-select
			label="<?php _e( 'Column template', 'jet-engine' ); ?>"
			description="<?php _e( 'Select template to use as base template for current column items', 'jet-engine' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			:options-list="listingTemplates"
			size="fullwidth"
			v-if="column.content && 'template' === column.content"
			:value="columnsList[ index ].template_id"
			@input="setFieldProp( column._id, 'template_id', $event, columnsList )"
		></cx-vui-select>
		<cx-vui-switcher
			label="<?php _e( 'Customize Column Output', 'jet-engine' ); ?>"
			description="<?php _e( 'Change format of column content.', 'jet-engine' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			v-if="column.content && 'raw' === column.content"
			:value="columnsList[ index ].customize"
			@input="setFieldProp( column._id, 'customize', $event, columnsList )"
		></cx-vui-switcher>
		<cx-vui-textarea
			label="<?php _e( 'Output Format', 'jet-engine' ); ?>"
			description="<?php _e( 'Columns output format. Use <b>%s</b> to pass column value. Use <b>%1$s</b> to pass column value multiple times.', 'jet-engine' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			size="fullwidth"
			v-if="column.content && 'raw' === column.content && column.customize"
			:value="columnsList[ index ].customize_format"
			@input="setFieldProp( column._id, 'customize_format', $event, columnsList )"
		></cx-vui-textarea>
	</cx-vui-repeater-item>
</cx-vui-repeater>
