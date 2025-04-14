(function( $ ) {

	'use strict';

	Vue.component( 'jet-engine-timber-macros', {
		name: 'jet-engine-timber-macros',
		template: `<div
			class="jet-engine-timber-macros"
			v-click-outside.capture="onClickOutside"
			v-click-outside:mousedown.capture="onClickOutside"
			v-click-outside:touchstart.capture="onClickOutside"
			@keydown.esc="onClickOutside"
			tabindex="-1"
		>
		<div style="cursor: pointer;" class="jet-engine-timber-macros__trigger" @click="switchIsActive()">
			<svg v-if="! isActive && ! result.macrosName" class="jet-query-macros__trigger-open" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><rect x="0" fill="none" width="20" height="20"/><g><path d="M14 10c2.2 0 4-1.8 4-4s-1.8-4-4-4-4 1.8-4 4 1.8 4 4 4zm-1-5V3h2v2h2v2h-2v2h-2V7h-2V5h2zM9 6c0-1.6.8-3 2-4h-1c-3.9 0-7 .9-7 2 0 1 2.6 1.8 6 2zm1 9c-3.9 0-7-.9-7-2v3c0 1.1 3.1 2 7 2s7-.9 7-2v-3c0 1.1-3.1 2-7 2zm2.8-4.2c-.9.1-1.9.2-2.8.2-3.9 0-7-.9-7-2v3c0 1.1 3.1 2 7 2s7-.9 7-2v-2c-.9.7-1.9 1-3 1-.4 0-.8-.1-1.2-.2zM10 10h1c-1-.7-1.7-1.8-1.9-3C5.7 6.9 3 6 3 5v3c0 1.1 3.1 2 7 2z"/></g></svg>
			<svg v-if="! isActive && result.macrosName" class="jet-query-macros__trigger-open" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><rect x="0" fill="none" width="20" height="20"/><g><path d="M9 6c0-1.6.8-3 2-4h-1c-3.9 0-7 .9-7 2 0 1 2.6 1.8 6 2zm3.8 4.8c-.9.1-1.9.2-2.8.2-3.9 0-7-.9-7-2v3c0 1.1 3.1 2 7 2s7-.9 7-2v-2c-.9.7-1.9 1-3 1-.4 0-.8-.1-1.2-.2zM10 15c-3.9 0-7-.9-7-2v3c0 1.1 3.1 2 7 2s7-.9 7-2v-3c0 1.1-3.1 2-7 2zm0-5h1c-1-.7-1.7-1.8-1.9-3C5.7 6.9 3 6 3 5v3c0 1.1 3.1 2 7 2zm4 0c2.2 0 4-1.8 4-4s-1.8-4-4-4-4 1.8-4 4 1.8 4 4 4zm-2.3-4.4l1.7 1.7 2.9-2.9.7.7-3.6 3.6L11 6.3l.7-.7z"/></g></svg>
			<svg v-if="isActive" class="jet-query-macros__trigger-close" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><rect x="0" fill="none" width="20" height="20"/><g><path d="M14.95 6.46L11.41 10l3.54 3.54-1.41 1.41L10 11.42l-3.53 3.53-1.42-1.42L8.58 10 5.05 6.47l1.42-1.42L10 8.58l3.54-3.53z"/></g></svg>
		</div>
			<div class="jet-engine-timber-macros__editor" v-if="isActive">
			
				<div class="jet-engine-timber-macros__config">
					<span class="jet-engine-timber-macros__config-trigger" v-if="! editSettings" @click="advancedSettingsPanel( true )">Advanced settings</span>
					<span class="jet-engine-timber-macros__config-trigger" v-else @click="advancedSettingsPanel( false )">Back</span>
				</div>
				<div class="jet-engine-timber-macros__content" v-if="editSettings">
					<cx-vui-select
						label="Context"
						:wrapper-css="[ 'mini-label' ]"
						:options-list="contextList"
						size="fullwidth"
						v-model="advancedSettings.context"
					></cx-vui-select>
					<cx-vui-input
						label="Fallback"
						:wrapper-css="[ 'mini-label' ]"
						size="fullwidth"
						v-model="advancedSettings.fallback"
					></cx-vui-input>
					<cx-vui-input
						label="Before"
						:wrapper-css="[ 'mini-label' ]"
						size="fullwidth"
						v-model="advancedSettings.before"
					></cx-vui-input>
					<cx-vui-input
						label="After"
						:wrapper-css="[ 'mini-label' ]"
						size="fullwidth"
						v-model="advancedSettings.after"
					></cx-vui-input>
				</div>
				<div class="jet-engine-timber-macros__content" v-else-if="editMacros">
					<div class="jet-engine-timber-macros__title">
						<span class="jet-engine-timber-macros__back" @click="resetEdit()">All Macros</span> > {{ currentMacros.name }}:
					</div>
					<div class="jet-engine-timber-macros__controls">
						<div class="jet-engine-timber-macros__control" v-for="control in getPreparedControls()">
							<component
								:is="control.type"
								:options-list="control.optionsList"
								:groups-list="control.groupsList"
								:label="control.label"
								:wrapper-css="[ 'mini-label' ]"
								size="fullwidth"
								v-if="checkCondition( control.condition )"
								v-model="result[ control.name ]"
							/>
						</div>
					</div>
					<cx-vui-button
						button-style="accent"
						size="mini"
						@click="applyMacros( false, true )"
					><span slot="label">Apply</span></cx-vui-button>
				</div>
				<div class="jet-engine-timber-macros__content" v-else-if="! editMacros && ! editSettings">
					<input
						class="jet-engine-timber-macros__search cx-vui-input size-fullwidth"
						type="search"
						placeholder="Enter keyword search"
						v-model="searchKeyword"
					>
					<div class="jet-engine-timber-macros__list" style="max-height: 300px; overflow-y: auto;">
						<div class="jet-engine-timber-macros-item" v-for="macros in filteredMacrosList" :key="macros.id">
							<div class="jet-engine-timber-macros-item__name" @click="applyMacros( macros )">
								<span class="jet-engine-timber-macros-item__mark">≫</span>
								{{ macros.name }}
							</div>
						</div>
					</div>
				</div>
				
			</div>
		</div>`,
		directives: { clickOutside: window.JetVueUIClickOutside },
		data: function() {
			return {
				isActive: false,
				macrosList: window.JetEngineDynamicVisibilityData.macros_list,
				contextList: window.JetEngineDynamicVisibilityData.context_list,
				currentMacros: {},
				editMacros: false,
				editSettings: false,
				result: {},
				advancedSettings: {},
				searchKeyword: '',
			};
		},
		props: ['controlName'],
		created: function() {
			
			if ( 'string' !== typeof this.value || ! this.value.includes( '%' ) ) {
				this.$set( this.advancedSettings, 'context', 'default_object' );
				return;
			}

			const regexp = /%([a-z_-]+)(\|[a-zA-Z0-9_\-\,\.\+\:\/\s\'\"\=\?\!\|\]\[\{\}%&]*)?%(\{.+\})?/;
			const parsedData = this.value.match( regexp ) || [];
			
			let macros = parsedData[1] || null;
			let data = null;

			if ( ! macros ) {
				console.warn( this.value + ' - incorrect macros value' );
				return;
			}

			if ( parsedData[2] ) {
				data = parsedData[2].substring( 1, parsedData[2].length );
				data = data.split( '|' );
			} else {
				data = [];
			}

			if ( parsedData[3] ) {
				this.advancedSettings = JSON.parse( parsedData[3] );
			} else {
				this.$set( this.advancedSettings, 'context', 'default_object' );
			}

			for ( var i = 0; i < this.macrosList.length; i++ ) {

				if ( macros === this.macrosList[ i ].id ) {

					this.result = {
						macros: macros,
						macrosName: this.macrosList[ i ].name,
						macrosControls: this.macrosList[ i ].controls,
					};

					if ( data.length && this.macrosList[ i ].controls ) {
						let index = 0;
						for ( const prop in this.macrosList[ i ].controls ) {

							if ( data[ index ] ) {
								this.$set( this.result, prop, data[ index ] );
							}

							index++;

						}
					}

					return;
				}
			}

		},
		computed: {
			filteredMacrosList: function () {

				let result = [ ...this.macrosList ];

				if ( this.searchKeyword ) {
					let searchKeyword = this.searchKeyword.toLowerCase();

					result = result.filter( ( item ) => {
						return ( item?.name && -1 !== item.name.toLowerCase().indexOf( searchKeyword ) );
					} );
				}

				return result;
			}
		},
		watch: {
			advancedSettings: {
				handler: function( newSettings ) {
					this.$set( this.result, 'advancedSettings', newSettings );
					this.emitMacroApply( false );
				},
				deep: true,
			},
			result: {
				handler: function( newSettings ) {
					this.$emit( 'input', this.formatResult(), this.$props.controlName );
				},
				deep: true,
			}
		},
		methods: {
			advancedSettingsPanel: function( state ) {
				this.editSettings = state;
			},
			applyMacros: function( macros, force ) {

				force = force || false;
				this.searchKeyword = '';

				if ( macros ) {
					this.$set( this.result, 'macros', macros.id );
					this.$set( this.result, 'macrosName', macros.name );

					if ( macros.controls ) {
						this.$set( this.result, 'macrosControls', macros.controls );
					} else {
						this.$set( this.result, 'macrosControls', {} );
					}
				}

				if ( macros && ! force && macros.controls ) {
					this.editMacros = true;
					this.currentMacros = macros;
					return;
				}

				this.emitMacroApply();	

			},
			emitMacroApply: function( deactivate = true ) {
				setTimeout( function() {
					this.$emit( 'macro-apply', this.formatResult(), this.$props.controlName, this.result?.macrosName );
					if ( deactivate ) {
						this.isActive = false;
					}
				}.bind( this ) );
			},
			switchIsActive: function() {

				this.isActive = ! this.isActive;

				if ( this.isActive ) {
					if ( this.result.macros ) {
						for (var i = 0; i < this.macrosList.length; i++) {
							if ( this.result.macros === this.macrosList[ i ].id && this.macrosList[ i ].controls ) {
								this.currentMacros = this.macrosList[ i ];
								this.editMacros = true;
							}
						}
					}
				} else {
					this.resetEdit();
				}

			},
			clearResult: function() {
				this.result = {};
				this.$emit( 'input', '' );
			},
			formatResult: function() {

				if ( ! this.result.macros ) {
					return;
				}

				let res = '%';
				res += this.result.macros;

				if ( this.result.macrosControls ) {
					for ( const prop in this.result.macrosControls ) {
						res += '|';

						if ( undefined !== this.result[ prop ] ) {
							res += this.result[ prop ];
						}

					}
				}

				res += '%';

				if ( this.result.advancedSettings && ( this.result.advancedSettings.fallback || this.result.advancedSettings.context ) ) {
					res += JSON.stringify( this.result.advancedSettings );
				}

				return res;

			},
			onClickOutside: function() {
				this.isActive = false;
				this.editMacros = false;
				this.advancedSettingsPanel( false );
				this.currentMacros = {};
			},
			resetEdit: function() {
				this.editMacros = false;
				this.advancedSettingsPanel( false );
				this.currentMacros = {};
			},
			getPreparedControls: function() {

				let controls = [];

				for ( const controlID in this.currentMacros.controls ) {

					let control     = this.currentMacros.controls[ controlID ];
					let optionsList = [];
					let type        = control.type;
					let label       = control.label;
					let defaultVal  = control.default;
					let groupsList  = [];
					let condition   = control.condition || {};

					switch ( control.type ) {

						case 'text':
							type = 'cx-vui-input';
							break;

						case 'textarea':
							type = 'cx-vui-textarea';
							break;

						case 'select':

							type = 'cx-vui-select';

							if ( control.groups ) {

								for ( var i = 0; i < control.groups.length; i++) {

									let group = control.groups[ i ];
									let groupOptions = [];

									for ( const optionValue in group.options ) {
										groupOptions.push( {
											value: optionValue,
											label: group.options[ optionValue ],
										} );
									}

									groupsList.push( {
										label: group.label,
										options: groupOptions,
									} );

								}
							} else {
								for ( const optionValue in control.options ) {
									optionsList.push( {
										value: optionValue,
										label: control.options[ optionValue ],
									} );
								}
							}

							break;
					}

					controls.push( {
						type: type,
						name: controlID,
						label: label,
						default: defaultVal,
						optionsList: optionsList,
						groupsList: groupsList,
						condition: condition,
					} );

				}

				return controls;
			},
			checkCondition: function( condition ) {

				let checkResult = true;

				condition = condition || {};

				for ( const [ fieldName, check ] of Object.entries( condition ) ) {
					if ( check && check.length ) {
						if ( ! check.includes( this.result[ fieldName ] ) ) {
							checkResult = false;
						}
					} else {
						if ( check != this.result[ fieldName ] ) {
							checkResult = false;
						}
					}
				}

				return checkResult;

			}
		},

	} );

	Vue.component( 'jet-engine-timber-editor-conditions', {
		template: `<div 
			class="jet-engine-timber-editor-conditions jet-engine-timber-dynamic-data"
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
				<svg 
					slot="label"
					viewBox="0 0 64 64" 
					xmlns="http://www.w3.org/2000/svg" 
					width="16"
					height="16"
					fill-rule="evenodd" 
					clip-rule="evenodd" 
					stroke-linejoin="round" 
					stroke-miterlimit="1.414"
				><path d="M11.375 20.844c-1.125 0-1.875.75-1.875 1.875s.75 1.875 1.875 1.875c3.75 0 7.5 1.5 10.125 4.125.75.75 1.875.75 2.625 0s.75-1.875 0-2.625c-3.375-3.375-8.063-5.25-12.75-5.25z" fill="#0071a1" fill-rule="nonzero"></path> <path d="M53.938 21.219l-5.25-5.25c-.376-.375-.938-.563-1.313-.563-.563 0-.938.188-1.312.563-.75.75-.75 1.875 0 2.625l2.062 2.062h-4.313c-4.875 0-9.375 1.875-12.75 5.25l-9.375 9.375c-2.625 2.625-6.375 4.125-10.125 4.125-1.125 0-1.875.75-1.875 1.875s.75 1.875 1.875 1.875c4.688 0 9.375-1.875 12.75-5.25l9.375-9.375c2.813-2.625 6.375-4.125 10.125-4.125h4.313l-2.062 2.063c-.75.75-.75 1.875 0 2.625s1.875.75 2.625 0l5.25-5.25c.75-.563.75-1.875 0-2.625z" fill="#0071a1" fill-rule="nonzero"></path> <path d="M53.938 40.156l-5.25-5.25c-.376-.375-.938-.562-1.313-.562-.563 0-.938.187-1.312.562-.75.75-.75 1.875 0 2.625l2.062 2.063h-4.313c-3.75 0-7.5-1.5-10.125-4.125-.374-.375-.937-.563-1.312-.563-.563 0-.938.188-1.312.563-.75.75-.75 1.875 0 2.625 3.374 3.375 7.874 5.25 12.75 5.25h4.312l-2.063 2.062c-.75.75-.75 1.875 0 2.625s1.876.75 2.625 0l5.25-5.25c.75-.562.75-1.875 0-2.625z" fill="#0071a1" fill-rule="nonzero"></path></svg>
				<span slot="label">Conditional Tags</span>
			</cx-vui-button>
			<div
				class="jet-engine-timber-dynamic-data__popup jet-engine-timber-editor-popup"
				v-if="showPopup"
				tabindex="-1"
			>
				<template v-if="isEnabled">
					<div class="jet-engine-timber-dynamic-data__notice">
						<span>*</span>
						<span v-html="macrosNotice"></span>
					</div>
					<div
						class="jet-engine-timber-dynamic-data__single-item-control"
						v-for="control in getPreparedControls( controls )"
					>
						<div class="jet-engine-timber-dynamic-data__control-row">
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
							>
								<jet-engine-timber-macros
									v-if="isMacrosInput( control )"
									:control-name="control.name"
									@macro-apply="applyMacro"
								></jet-engine-timber-macros>
							</component>
							
						<div>
						<small v-if="control.description && checkCondition( control.condition, result )" v-html="control.description"></small>
					</div>
				</template>
				<template v-else>
					<div class="jet-engine-timber-dynamic-data__notice">
						<span>*</span>
						<span v-html="disabledNotice"></span>
					</div>
				</template>
				<div class="jet-engine-timber-dynamic-data__single-actions">
					<cx-vui-button
						button-style="accent"
						size="mini"
						@click="insertConditionalTag()"
					><span slot="label">{{ insertButtonLabel() }}</span></cx-vui-button>
				</div>
				<br>
				<div class="jet-engine-timber-dynamic-data__notice">
					<span>*</span>
					<span v-html="twigNotice"></span>
				</div>
			</div>
		</div>`,
		directives: { clickOutside: window.JetVueUIClickOutside },
		mixins: [ window.popupHelper, window.controlsHelper ],
		data() {
			return {
				controls: window.JetEngineDynamicVisibilityData.controls,
				functionName: window.JetEngineDynamicVisibilityData.function_name,
				macrosNotice: window.JetEngineDynamicVisibilityData.macros_notice,
				twigNotice: window.JetEngineDynamicVisibilityData.twig_notice,
				isEnabled: window.JetEngineDynamicVisibilityData.is_enabled,
				disabledNotice: window.JetEngineDynamicVisibilityData.disabled_notice,
				result: {},
			};
		},
		created() {
			window.JetEngineDynamicVisibilityData.conditionEditor = this;
		},
		methods: {
			onPopupClose() {
				this.result = {};
			},
			isMacrosInput( control ) {
				if ( ! control?.name ) {
					return false;
				}

				const allowed = [
					'jedv_field',
					'jedv_value',
				];

				return allowed.includes( control.name );
			},
			applyMacro( macro, controlName, macroName ) {				
				this.$set( this.result, controlName, macro );	
			},
			showMacrosList() {
				return;
			},
			insertConditionalTag() {

				this.$emit( 'insert', this.getConditionalTagToInsert() );
				this.closePopup();

			},
			insertButtonLabel() {
				if ( this.isEmpty() ) {
					return 'Insert empty tag';
				} else {
					return 'Insert';
				}
			},
			isEmpty() {
				return ! this.result.jedv_condition ? true : false;
			},
			getConditionalTagToInsert() {

				let args = {};

				for ( const arg in this.result ) {

					let allowed = this.controls[ arg ].condition ? false : true;

					if ( ! allowed && this.checkCondition( this.controls[ arg ].condition, this.result ) ) {
						allowed = true;
					}

					if ( allowed ) {
						args[ arg ] = this.result[ arg ];
					}

				}

				let result = '{% if ';

				if ( this.isEmpty() ) {
					result += ' ';
				} else {
					result += this.functionName + '(args=';
					result += JSON.stringify( args );
					result += ')';
				}
				
				result += ' %}';
				result += "\n<!-- Paste your HTML here -->\n";
				result += '{% endif %}';

				return result;
			}
		}

	} );

})( jQuery );
