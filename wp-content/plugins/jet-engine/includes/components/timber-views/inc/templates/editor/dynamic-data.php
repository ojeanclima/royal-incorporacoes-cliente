<div
	class="jet-engine-timber-dynamic-data"
	v-click-outside.capture="closePopup"
	v-click-outside:mousedown.capture="closePopup"
	v-click-outside:touchstart.capture="closePopup"
	@keydown.esc="closePopup"
>
	<cx-vui-button
		button-style="accent-border"
		@click="switchPopup"
		size="mini"
	>
		<slot slot="label"></slot>
	</cx-vui-button>
	<div
		class="jet-engine-timber-dynamic-data__popup jet-engine-timber-editor-popup"
		v-if="showPopup"
		tabindex="-1"
	>
		<div v-if="'functions' == currentMode">
			<div class="jet-engine-timber-dynamic-data__single-item" v-if="currentFunction">
				<div class="jet-engine-timber-dynamic-data__single-item-title">
					<span 
						class="jet-engine-timber-dynamic-data__single-item-back" 
						@click="resetEdit()"><?php 
							_e( 'All Functions', 'jet-engine' );
						?></span> > {{ currentFunction.label }}:
				</div>
				<div class="jet-engine-timber-dynamic-data__single-item-controls">
					<div
						v-if="! currentFunction.chained && currentFunction.args"
						class="jet-engine-timber-dynamic-data__single-item-control"
						v-for="control in getPreparedControls( currentFunction.args )"
					>
						<component
							:is="control.type"
							:options-list="control.optionsList"
							:groups-list="control.groupsList"
							:label="control.label"
							:wrapper-css="[ 'mini-label' ]"
							:multiple="control.multiple"
							size="fullwidth"
							v-if="checkCondition( control.condition, result )"
							v-model="result[ control.name ]"
						><small v-if="control.description" v-html="control.description"></small></component>
					</div>
					<div
						v-if="currentFunction.chained"
						class="jet-engine-timber-dynamic-data__single-item-control is-chained"
					>
						<jet-engine-timber-chained-control
							depth="0"
							:children="currentFunction.children"
							v-model="chainedResult"
						></jet-engine-timber-chained-control>
					</div>
				</div>
				<div class="jet-engine-timber-dynamic-data__single-actions">
					<cx-vui-button
						button-style="accent-border"
						size="mini"
						@click="goToFilter()"
					><span slot="label"><?php _e( 'Add filter to result', 'jet-engine' ); ?></span></cx-vui-button>
					or
					<cx-vui-button
						button-style="accent"
						size="mini"
						@click="insertFunction()"
					><span slot="label"><?php _e( 'Insert', 'jet-engine' ); ?></span></cx-vui-button>
				</div>
			</div>
			<div v-else>
				<h4><?php _e( 'JetEngine', 'jet-engine' ); ?></h4>
				<div class="jet-engine-timber-dynamic-data__list">
					<div 
						class="jet-engine-timber-dynamic-data__item" 
						v-for="( functionData, functionName ) in functions" 
						v-if="'jet-engine' === functionData.source"
						@click="selectFunction( functionData )"
					>
						<span class="jet-engine-timber-dynamic-data__item-mark">≫</span>
						{{ functionData.label }}
					</div>
				</div>
				<h4><?php _e( 'Default Data', 'jet-engine' ); ?></h4>
				<div 
					class="jet-engine-timber-dynamic-data__item" 
					v-for="( functionData, functionName ) in functions" 
					v-if="'default' === functionData.source"
					@click="selectFunction( functionData )"
				>
					<span class="jet-engine-timber-dynamic-data__item-mark">≫</span>
					{{ functionData.label }}
				</div>
			</div>
		</div>
		<div v-else-if="'filters' == currentMode">
			<div class="jet-engine-timber-dynamic-data__single-item" v-if="currentFilter">
				<div class="jet-engine-timber-dynamic-data__single-item-title">
					<span 
						class="jet-engine-timber-dynamic-data__single-item-back" 
						@click="resetEdit()"><?php 
							_e( 'All Filters', 'jet-engine' );
						?></span> > {{ currentFilter.label }}:
				</div>
				<div class="jet-engine-timber-dynamic-data__notice" style="padding-top: 10px;" v-if="currentFilter.note">
					<span>*</span>
					<span v-html="currentFilter.note"></span>
				</div>
				<div class="jet-engine-timber-dynamic-data__single-item-controls">
					<div
						class="jet-engine-timber-dynamic-data__single-item-control"
						v-for="control in getPreparedControls( currentFilter.args )"
					>
						<component
							:is="control.type"
							:options-list="control.optionsList"
							:groups-list="control.groupsList"
							:label="control.label"
							:wrapper-css="[ 'mini-label' ]"
							:multiple="control.multiple"
							size="fullwidth"
							v-if="checkCondition( control.condition, filterResult )"
							v-model="filterResult[ control.name ]"
						><small v-if="control.description" v-html="control.description"></small></component>
					</div>
				</div>
				<div class="jet-engine-timber-dynamic-data__single-actions">
					<cx-vui-button
						button-style="accent"
						size="mini"
						@click="insertFilter()"
					><span slot="label"><?php _e( 'Insert', 'jet-engine' ); ?></span></cx-vui-button>
				</div>
			</div>
			<div v-else>
				<div class="jet-engine-timber-dynamic-data__single-item-title with-indent" v-if="'functions' == mode">
					<span 
						class="jet-engine-timber-dynamic-data__single-item-back" 
						@click="resetEdit( 'functions' )"><?php 
							_e( '< Back to Functions', 'jet-engine' );
						?></span>
				</div>
				<div class="jet-engine-timber-dynamic-data__list">
					<div 
						class="jet-engine-timber-dynamic-data__item" 
						v-for="( filterData, filterName ) in filters" 
						@click="selectFilter( filterData )"
					>
						<span class="jet-engine-timber-dynamic-data__item-mark">≫</span>
						{{ filterData.label }}
					</div>
				</div>
			</div>
		</div>
	</div>
</div>