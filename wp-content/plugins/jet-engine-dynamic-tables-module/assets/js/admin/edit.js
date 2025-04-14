(function( $, JetEngineTableConfig ) {

	'use strict';

	Vue.component( 'jet-table-switcher', {
		name: 'jet-table-switcher',
		template: '#jet-table-switcher',
		props: {
			initialState: {
				type: String,
				default: '',
			},
			sLabel: {
				type: String,
				default: '',
			},
			eLabel: {
				type: String,
				default: '',
			},
		},
		data() {
			return {
				state: 's',
			};
		},
		created() {

			if ( this.initialState ) {
				this.state = this.initialState;
			}

		}
	} );

	Vue.component( 'jet-table-columns', {
		name: 'jet-table-columns',
		template: '#jet-table-columns',
		props: [ 'value', 'allowedColumns' ],
		data() {
			return {
				columnsList: [],
				contentTypes: JetEngineTableConfig.content_types,
				objects: JetEngineTableConfig.object_fields,
				fieldsList: JetEngineTableConfig.meta_fields,
				callbacks: JetEngineTableConfig.callbacks,
				callbacksArgs: JetEngineTableConfig.callback_args,
				listingTemplates: JetEngineTableConfig.listing_templates,
			};
		},
		created() {

			let val = [];

			if ( this.value && this.value.length ) {
				val = this.value;
			}

			this.columnsList = [ ...val ];

		},
		methods: {
			onInput() {
				this.$emit( 'input', this.columnsList );
			},
			allowedColumnsForOptions() {

				const res = [ {
					value: '',
					label: 'Select column...',
				} ];

				for ( var i = 0; i < this.allowedColumns.length; i++ ) {
					res.push( {
						value: this.allowedColumns[i],
						label: this.allowedColumns[i],
					} );
				}
				return res;
			},
			allowedDataSources() {

				let sources = [ {
					value: '',
					label: 'Select source...',
				} ];

				for ( const property in JetEngineTableConfig.data_sources ) {

					if ( 'fetched' === property && ( ! this.allowedColumns || ! this.allowedColumns.length ) ) {
						continue;
					}

					sources.push( {
						value: property,
						label: JetEngineTableConfig.data_sources[ property ],
					} );

				}

				return sources;

			},
			addNewField( event, props, parent, callback ) {

				props = props || [];

				var field = {};

				for (var i = 0; i < props.length; i++) {
					field[ props[ i ] ] = '';
				}

				field._id = Math.round( Math.random() * 1000000 );
				field.collapsed = false;

				parent.push( field );

				if ( callback && 'function' === typeof callback ) {
					callback( field, parent );
				}

				this.onInput();

			},
			setFieldProp( id, key, value, parent ) {

				let index = this.searchByID( id, parent );

				if ( false === index ) {
					return;
				}

				let field = parent[ index ];

				field[ key ] = value;

				parent.splice( index, 1, field );

				this.onInput();

			},
			cloneField( index, id, parent, callback ) {

				let field = JSON.parse( JSON.stringify( parent[ index ] ) );

				field.collapsed = false;
				field._id = Math.round( Math.random() * 1000000 );

				parent.splice( index + 1, 0, field );

				if ( callback && 'function' === typeof callback ) {
					callback( field, parent, id );
				}

				this.onInput();

			},
			deleteField( index, id, parent, callback ) {

				index = this.searchByID( id, parent );

				if ( false === index ) {
					return;
				}

				parent.splice( index, 1 );

				if ( callback && 'function' === typeof callback ) {
					callback( id, index, parent );
				}

				this.onInput();

			},
			isCollapsed( parent ) {
				if ( undefined === parent.collapsed || true === parent.collapsed ) {
					return true;
				} else {
					return false;
				}
			},
			searchByID( id, list ) {

				for ( var i = 0; i < list.length; i++ ) {
					if ( id == list[ i ]._id ) {
						return i;
					}
				}

				return false;

			}
		}
	} );

	var JetEngineTable = new Vue( {
		el: '#jet_table_form',
		template: '#jet-table-form',
		data: {
			generalSettings: {},
			metaFields: [],
			buttonLabel: JetEngineTableConfig.edit_button_label,
			isEdit: JetEngineTableConfig.item_id,
			helpLinks: JetEngineTableConfig.help_links,
			queries: JetEngineTableConfig.queries,
			showDeleteDialog: false,
			saving: false,
			suggestions: [],
			updatingPreview: false,
			previewCount: 0,
			previewBody: null,
			fetchingData: false,
			errors: {
				name: false,
			},
			errorNotices: [],
			fieldsLoaded: 'not-loaded',
			isReloadingPreview: false,
		},
		created() {

			var self = this;

			if ( JetEngineTableConfig.item_id ) {

				var params = new URLSearchParams();

				params.append( 'instance', JetEngineTableConfig.instance );

				wp.apiFetch( {
					method: 'get',
					path: JetEngineTableConfig.api_path_get + JetEngineTableConfig.item_id + '?' + params.toString(),
				} ).then( function( response ) {

					if ( response.success && response.data ) {

						for ( const property in response.data ) {

							if ( 'meta_fields' === property ) {

								let fields = response.data.meta_fields;

								if ( ! fields || ! fields.length ) {
									fields = [];
								}

								for (var i = 0; i < fields.length; i++) {
									self.metaFields.push( { ...fields[ i ] } );
								}

							} else {
								self.$set( self.generalSettings, property, response.data[ property ] );
							}

						}

						self.fieldsLoaded = 'loaded';

						if ( ! self.generalSettings.inline_styles ) {
							self.$set( self.generalSettings, 'inline_styles', {} );
						}

					} else {
						if ( response.notices.length ) {
							response.notices.forEach( function( notice ) {
								self.$CXNotice.add( {
									message: notice.message,
									type: 'error',
									duration: 15000,
								} );
								//self.errorNotices.push( notice.message );
							} );
						}
					}
				} ).catch( function( e ) {
					self.$CXNotice.add( {
						message: e.message,
						type: 'error',
						duration: 7000,
					} );
				} );

			} else if ( ! this.generalSettings.inline_styles ) {
				this.$set( this.generalSettings, 'inline_styles', {} );
			}

		},
		watch: {
			metaFields( value ) {
				if ( value.length ) {

					for ( var i = 0; i < value.length; i++ ) {

						let _id = value[ i ]._id;

						if ( ! this.generalSettings.inline_styles[ _id ] ) {
							this.$set( this.generalSettings.inline_styles, _id, {
								width: '',
								v_align: '',
								h_align: '',
							} );
						}

					}

				}
			},
		},
		methods: {
			makeFetchDataRequest() {

				this.fetchingData = true;

				wp.apiFetch( {
					method: 'post',
					path: JetEngineTableConfig.api_path_fetch_data,
					data: {
						query_id: this.generalSettings.query_id,
					}
				} ).then( ( response ) => {

					if ( response.success ) {

						this.$set( this.generalSettings, 'allowed_columns', response.columns );

						this.$CXNotice.add( {
							message: 'Done!',
							type: 'success',
							duration: 2000,
						} );

					} else {
						this.$CXNotice.add( {
							message: response.message,
							type: 'error',
							duration: 20000,
						} );
					}

					this.fieldsLoaded = 'reloaded';
					this.fetchingData = false;

				} ).catch( ( response ) => {
					this.fetchingData = false;
					this.$CXNotice.add( {
						message: response.message,
						type: 'error',
						duration: 20000,
					} );

				} );

			},
			reloadPreview() {

				this.isReloadingPreview = true;

				jQuery.ajax({
					url: window.ajaxurl,
					type: 'POST',
					dataType: 'html',
					data: {
						action: 'jet_engine_data_table_preview',
						general_settings: this.generalSettings,
						meta_fields: this.metaFields,
					},
				}).done( ( response ) => {
					this.$refs.preview.innerHTML = response;
					this.isReloadingPreview = false;
				} ).fail( ( jqXHR, textStatus, errorThrown ) => {

					this.$CXNotice.add( {
						message: errorThrown,
						type: 'error',
						duration: 20000,
					} );

					this.isReloadingPreview = false;
				} );

			},
			applySuggestion( suggestion ) {
				this.$set( this.generalSettings, 'preview_page_title', suggestion.text );
				this.$set( this.generalSettings, 'preview_page', suggestion.id );
				this.suggestions = [];
			},
			ensureQueryType() {

				if ( this.generalSettings.table_type && ! this.generalSettings[ this.generalSettings.table_type ] ) {
					this.$set( this.generalSettings, this.generalSettings.table_type, {} );
				}

				if ( this.generalSettings.table_type && ! this.generalSettings[ '__dynamic_' + this.generalSettings.table_type ] ) {
					this.$set( this.generalSettings, '__dynamic_' + this.generalSettings.table_type, {} );
				}

			},
			handleFocus( where ) {

				if ( this.errors[ where ] ) {
					this.$set( this.errors, where, false );
					this.$CXNotice.close( where );
					//this.errorNotices.splice( 0, this.errorNotices.length );
				}

			},
			setDynamicQuery( prop, value ) {
				this.$set( this.generalSettings, prop, value );
			},
			save() {

				var self      = this,
					hasErrors = false,
					path      = JetEngineTableConfig.api_path_edit;

				if ( JetEngineTableConfig.item_id ) {
					path += JetEngineTableConfig.item_id;
				}

				for ( var errKey in this.errors ) {

					if ( ! self.generalSettings[ errKey ] ) {
						self.$set( this.errors, errKey, true );

						self.$CXNotice.add( {
							message: JetEngineTableConfig.notices[ errKey ],
							type: 'error',
							duration: 7000,
						}, 'name' );

						//self.errorNotices.push( JetEngineCCTConfig.notices.name );
						hasErrors = true;
					}

				}

				if ( hasErrors ) {
					return;
				}

				self.saving = true;

				let params = new URLSearchParams();

				params.append( 'instance', JetEngineTableConfig.instance );

				wp.apiFetch( {
					method: 'post',
					path: path + '?' + params.toString(),
					data: {
						general_settings: self.generalSettings,
						meta_fields: self.metaFields,
					}
				} ).then( function( response ) {

					if ( response.success ) {

						if ( JetEngineTableConfig.redirect ) {
							window.location = JetEngineTableConfig.redirect.replace( /%id%/, response.item_id );
						} else {

							self.$CXNotice.add( {
								message: JetEngineTableConfig.notices.success,
								type: 'success',
							} );

							self.saving = false;
						}

					} else {
						if ( response.notices && response.notices.length ) {
							response.notices.forEach( function( notice ) {

								self.$CXNotice.add( {
									message: notice.message,
									type: 'error',
									duration: 7000,
								} );

							} );

							self.saving = false;
						} else if ( response.message ) {
							self.$CXNotice.add( {
								message: response.message,
								type: 'error',
								duration: 7000,
							} );
						}
					}
				} ).catch( function( response ) {

					self.$CXNotice.add( {
						message: response.message,
						type: 'error',
						duration: 7000,
					} );

					self.saving = false;
				} );

			},
		}
	} );

})( jQuery, window.JetEngineTableConfig );
