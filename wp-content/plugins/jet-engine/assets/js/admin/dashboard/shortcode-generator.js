Vue.component( 'jet-engine-shortcode-generator', {
	name: 'jet-engine-shortcode-generator',
	template: '#jet-engine-shortcode-generator',
	data: function() {
		const {
			sources,
			object_fields: objectFields,
			source_args: sourceArgs,
			meta_fields: metaFields,
			options_pages: optionsPages,
			callbacks,
			cb_args: cbArgs,
			context_list: contextList,
			labels,
			shortcode_types: shortcodeTypes,
			tag_type: tagType,
		} = window.JetEngineDashboardConfig.shortode_generator;

		return {
			sources,
			objectFields,
			sourceArgs,
			metaFields,
			optionsPages,
			callbacks,
			cbArgs,
			contextList,
			labels,
			shortcodeTypes,
			tagType,
			shortcode: '[jet_engine_data ]',
			controls: {},
			showCopyShortcode: undefined !== navigator.clipboard && undefined !== navigator.clipboard.writeText,
			attrs: {
				shortcode_types: 'jet_engine_data',
				tag_type: 'selfclosed',
			},
			copied: false,
		};
	},
	created: function() {

		const addControl = (name, config) => this.addControl(name, config);

		if ( 1 < this.shortcodeTypes.length ) {
			addControl( 'shortcode_types', {
				label: this.labels.shortcode_types.label,
				description: this.labels.shortcode_types.description,
				type: 'select',
				default: 'jet_engine_data',
				options: this.shortcodeTypes,
			} );
		}

		const conditionJetEngineData = {
			'shortcode_types': 'jet_engine_data',
		};

		addControl( 'dynamic_field_source', {
			label: this.labels.dynamic_field_source.label,
			type: 'select',
			default: 'object',
			options: this.sources,
			condition: conditionJetEngineData,
		} );

		addControl( 'dynamic_field_post_object', {
			label: this.labels.dynamic_field_post_object.label,
			type: 'select',
			default: 'post_title',
			groups: this.objectFields,
			condition: {
				...conditionJetEngineData,
				'dynamic_field_source': 'object',
			},
		} );

		addControl( 'dynamic_field_wp_excerpt', {
			label: this.labels.dynamic_field_wp_excerpt.label,
			type: 'switcher',
			default: '',
			condition: {
				...conditionJetEngineData,
				'dynamic_field_source': 'object',
				'dynamic_field_post_object': 'post_excerpt',
			},
		} );

		addControl( 'dynamic_excerpt_more', {
			label: this.labels.dynamic_excerpt_more.label,
			type: 'text',
			default: '...',
			condition: {
				...conditionJetEngineData,
				'dynamic_field_source': 'object',
				'dynamic_field_post_object': 'post_excerpt',
			},
		} );

		addControl( 'dynamic_excerpt_length', {
			label: this.labels.dynamic_excerpt_length.label,
			type: 'text',
			default: 0,
			condition: {
				...conditionJetEngineData,
				'dynamic_field_source': 'object',
				'dynamic_field_post_object': 'post_excerpt',
			},
		} );

		addControl( 'dynamic_field_post_meta', {
			label: this.labels.dynamic_field_post_meta.label,
			type: 'select',
			default: '',
			groups: this.metaFields,
			condition: {
				...conditionJetEngineData,
				'dynamic_field_source': 'meta',
			},
		} );

		addControl( 'dynamic_field_option', {
			label: this.labels.dynamic_field_option.label,
			type: 'select',
			default: '',
			groups: this.optionsPages,
			condition: {
				...conditionJetEngineData,
				'dynamic_field_source': 'options_page',
			},
		} );

		addControl( 'dynamic_field_var_name', {
			label: this.labels.dynamic_field_var_name.label,
			type: 'text',
			default: '',
			condition: {
				...conditionJetEngineData,
				'dynamic_field_source': 'query_var',
			},
		} );

		if ( this.sourceArgs && this.sourceArgs.length ) {
			for (var i = 0; i < this.sourceArgs.length; i++) {
				addControl( this.sourceArgs[ i ].name, this.sourceArgs[ i ].data );
			}
		}

		addControl( 'dynamic_field_post_meta_custom', {
			label: this.labels.dynamic_field_post_meta_custom.label,
			type: 'text',
			default: '',
			description: this.labels.dynamic_field_post_meta_custom.description,
			condition: {
				...conditionJetEngineData,
				'dynamic_field_source!': [ 'query_var', 'options_page', 'relations_hierarchy' ],
			},
		} );

		addControl( 'hide_if_empty', {
			label: this.labels.hide_if_empty.label,
			type: 'switcher',
			default: '',
			condition: conditionJetEngineData,
		} );

		addControl( 'field_fallback', {
			label: this.labels.field_fallback.label,
			type: 'text',
			default: '',
			condition: {
				...conditionJetEngineData,
				'hide_if_empty': false,
			},
		} );

		addControl( 'dynamic_field_filter', {
			label: this.labels.dynamic_field_filter.label,
			type: 'switcher',
			default: '',
			condition: conditionJetEngineData,
		} );

		const repeaterFields = {};

		repeaterFields.filter_callback = {
			label: this.labels.filter_callback.label,
			type: 'select',
			default: '',
			options: this.callbacks,
			condition: conditionJetEngineData,
		};

		for ( const [ fieldName, fieldData ] of Object.entries( this.cbArgs ) ) {
			repeaterFields[ fieldName ] = fieldData;
		}

		addControl( 'filter_callbacks', {
			label: 'Applied Callbacks',
			type: 'repeater',
			title: 'filter_callback',
			fields: repeaterFields,
			condition: {
				...conditionJetEngineData,
				'dynamic_field_filter': 'yes',
			},
		} );

		this.$set( this.attrs, 'filter_callbacks', [] );

		addControl( 'dynamic_field_custom', {
			label: this.labels.dynamic_field_custom.label,
			type: 'switcher',
			default: '',
			condition: conditionJetEngineData,
		} );

		addControl( 'dynamic_field_format', {
			label: this.labels.dynamic_field_format.label,
			type: 'textarea',
			default: '%s',
			description: this.labels.dynamic_field_format.description,
			condition: {
				...conditionJetEngineData,
				'dynamic_field_custom': 'yes',
			},
		} );

		addControl( 'object_context', {
			label: this.labels.object_context.label,
			type: 'select',
			default: 'default_object',
			options: this.contextList,
			condition: conditionJetEngineData,
		} );

		addControl( 'tag_type', {
			label: this.labels.tag_type.label,
			description: this.labels.tag_type.description,
			type: 'select',
			default: 'selfclosed',
			options: this.tagType,
			condition: {
				'shortcode_types!': 'jet_engine_data',
			},
		} );

		window.JetPlugins.hooks.doAction( 'jetEngine.shortcodeGenerator.controls', addControl, this );
	},
	computed: {
		generatedShortcode: function() {

			let isEnclosing = false;
			let currentTag = this.attrs.shortcode_types || 'jet_engine_data';

			var result = '[' + currentTag;

			const attrsToParse = [];

			for ( const attr in this.attrs ) {

				if ( ! this.isVisible( this.controls[ attr ] ) ) {
					continue;
				}

				let value = this.attrs[ attr ];

				if ( 'shortcode_types' === attr ) {
					continue;
				}

				if ( 'tag_type' === attr ) {

					if ( 'enclosed' === value ) {
						isEnclosing = true;
					}

					continue;
				}

				if ( value === this.controls[ attr ].default ) {
					continue;
				}

				if ( value instanceof Array ) {

					let toString = [];

					for ( var i = 0; i < value.length; i++ ) {

						if ( ! value[ i ]
							|| 'object' !== typeof value[ i ]
							|| Array.isArray( value[ i ] ) ) {
							toString.push( value[ i ] );

							if ( ! attrsToParse.includes( attr ) ) {
								attrsToParse.push( attr );
							}
							continue;
						}

						let row = { ...value[ i ] };

						if ( undefined !== row._id ) {
							delete row._id;
						}

						if ( undefined !== row.collapsed ) {
							delete row.collapsed;
						}

						for ( const prop in row ) {
							let field = this.controls[ attr ].fields[ prop ]
							if ( ! this.isRepeaterFieldVisible( field, row ) ) {
								delete row[ prop ];
							}
						}

						row = new URLSearchParams( row );
						toString.push( '{' + row.toString() + '}' );
					}

					value = toString.join( ',' );
				} else {
					value = JSON.stringify( value );
				}

				result += ' ' + attr + '=' + value;

			}

			if ( attrsToParse.length ) {
				result += ' _parse_attrs=' + attrsToParse.join( ',' );
			}

			result += ']';

			if ( isEnclosing ) {
				result += 'Add your content here...[/' + currentTag + ']';
			}

			return result;

		},
	},
	methods: {
		addControl: function( name, data ) {

			const preparedControls = this.getPreparedControls( { [name]: data } );

			for ( var i = 0; i < preparedControls.length; i++ ) {
				this.$set( this.controls, preparedControls[ i ].name, preparedControls[ i ] );
			}

		},
		addNewItem: function( event, props, parent, control, callback ) {

			props = props || [];

			var field = {};

			for ( const prop in control.fields ) {
				if ( control.fields[ prop ].default ) {
					field[ prop ] = control.fields[ prop ].default;
				}
			}

			field._id = Math.round( Math.random() * 1000000 );
			field.collapsed = false;

			parent.push( field );

			if ( callback && 'function' === typeof callback ) {
				callback( field, parent );
			}

		},
		deleteItemProp: function( id, key, parent ) {

			let index = this.searchByID( id, parent );

			if ( false === index ) {
				return;
			}

			let field = parent[ index ];

			delete field[ key ];

			parent.splice( index, 1, field );
		},
		setItemProp: function( id, key, value, parent ) {

			let index = this.searchByID( id, parent );

			if ( false === index ) {
				return;
			}

			let field = parent[ index ];

			field[ key ] = value;

			parent.splice( index, 1, field );

		},
		cloneItem: function( index, id, parent, callback ) {

			let field = JSON.parse( JSON.stringify( parent[ index ] ) );

			field.collapsed = false;
			field._id = Math.round( Math.random() * 1000000 );

			parent.splice( index + 1, 0, field );

			if ( callback && 'function' === typeof callback ) {
				callback( field, parent, id );
			}

		},
		deleteItem: function( index, id, parent, callback ) {

			index = this.searchByID( id, parent );

			if ( false === index ) {
				return;
			}

			parent.splice( index, 1 );

			if ( callback && 'function' === typeof callback ) {
				callback( id, index, parent );
			}

		},
		isCollapsed: function( parent ) {
			if ( undefined === parent.collapsed || true === parent.collapsed ) {
				return true;
			} else {
				return false;
			}
		},
		searchByID: function( id, list ) {

			for ( var i = 0; i < list.length; i++ ) {
				if ( id == list[ i ]._id ) {
					return i;
				}
			}

			return false;

		},
		getPreparedControls: function( inputControls ) {

			controls = [];

			for ( const controlID in inputControls ) {

				let control     = inputControls[ controlID ];
				let optionsList = [];
				let type        = control.type;
				let label       = control.label;
				let description = control.description || '';
				let defaultVal  = control.default;
				let groupsList  = [];
				let condition   = control.condition || {};
				let fields      = false;
				let title       = false;
				let multiple    = false;
				let inputType   = 'text';

				switch ( control.type ) {

					case 'text':
						type = 'cx-vui-input';
						break;

					case 'number':
						type = 'cx-vui-input';
						inputType = 'number';
						break;

					case 'textarea':
						type = 'cx-vui-textarea';
						break;

					case 'switcher':
						type = 'cx-vui-switcher';
						if ( 'yes' === defaultVal || 'true' === defaultVal ) {
							defaultVal = true;
						} else {
							defaultVal = false;
						}
						break;

					case 'repeater':
						type = 'repeater';
						title = control.title;
						fields = control.fields;
						break;

					case 'select':
						type = 'cx-vui-select';

						if ( control.groups ) {
							groupsList = this.prepareGroupsList(control.groups);
						} else {
							optionsList = this.prepareOptionsList(control.options);
						}

						break;

					case 'select2':
						type = 'cx-vui-f-select';
						multiple = control.multiple;
						optionsList = this.prepareOptionsList(control.options);

						break;

				}

				if ( undefined === this.attrs[ controlID ] ) {
					this.$set( this.attrs, controlID, defaultVal );
				}

				controls.push( {
					type,
					name: controlID,
					label,
					description,
					default: defaultVal,
					optionsList,
					multiple,
					groupsList,
					condition,
					fields,
					title: control.title,
					inputType,
				} );

			}

			return controls;

		},
		prepareGroupsList: function(groups) {
			const groupsList = [];

			for ( var i = 0; i < groups.length; i++) {
				let group = groups[ i ];
				let groupOptions = [];

				if ( group.options ) {
					groupOptions = this.prepareOptionsList(group.options);
				} else if ( group.values ) {
					for ( var j = 0; j < group.values.length; j++ ) {
						groupOptions.push( group.values[ j ] );
					}
				}

				groupsList.push( {
					label: group.label,
					options: groupOptions,
				} );
			}

			return groupsList;
		},
		prepareOptionsList: function(options) {
			const optionsList = [];

			for ( const optionValue in options ) {
				if ( options[optionValue] && options[optionValue].value !== undefined ) {
					optionsList.push( {
						value: options[ optionValue ].value,
						label: options[ optionValue ].label,
					} );
				} else {
					optionsList.push( {
						value: optionValue,
						label: options[ optionValue ],
					} );
				}
			}

			return optionsList;
		},
		copyShortcodeToClipboard: function() {

			var self = this;

			navigator.clipboard.writeText( this.generatedShortcode ).then( function() {
				// clipboard successfully set
				self.copied = true;
				setTimeout( function() {
					self.copied = false;
				}, 2000 );
			}, function() {
				// clipboard write failed
			} );
		},
		isVisible: function( control ) {
			if ( ! control ) {
				return false;
			}
			if ( ! control.condition ) {
				return true;
			} else {
				return this.checkCondition( control.condition, this.attrs );
			}
		},
		isRepeaterFieldVisible: function( control, item ) {

			let res = false;

			if ( ! control ) {
				return false;
			}

			if ( ! control.condition ) {
				res = true;
			} else {
				res = this.checkCondition( control.condition, item );
			}

			return res;

		},
		checkCondition: function( condition, attrs ) {

			let checkResult = true;

			condition = condition || {};

			for ( const [ fieldName, check ] of Object.entries( condition ) ) {

				let isExcl = fieldName.includes( '!' );
				let valToCheck = check;

				if ( 'yes' === check ) {
					valToCheck = true;
				}

				if ( 'no' === check ) {
					valToCheck = false;
				}

				if ( isExcl ) {

					let rFieldName = fieldName.replace( '!', '' );

					if ( valToCheck && valToCheck.length && 'string' !== typeof valToCheck ) {
						if ( valToCheck.includes( attrs[ rFieldName ] ) || valToCheck.includes( this.attrs[ rFieldName ] ) ) {
							checkResult = false;
						}
					} else {
						if ( valToCheck == attrs[ rFieldName ] || valToCheck == this.attrs[ rFieldName ] ) {
							checkResult = false;
						}
					}

				} else {
					if ( valToCheck && valToCheck.length && 'string' !== typeof valToCheck ) {
						if ( ! valToCheck.includes( attrs[ fieldName ] ) && ! valToCheck.includes( this.attrs[ fieldName ] ) ) {
							checkResult = false;
						}
					} else {
						if ( valToCheck != attrs[ fieldName ] && valToCheck != this.attrs[ fieldName ] ) {
							checkResult = false;
						}
					}
				}

			}

			return checkResult;

		}
	},
} );
