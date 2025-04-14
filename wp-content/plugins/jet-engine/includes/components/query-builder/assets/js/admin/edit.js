(function( $, JetEngineQueryConfig ) {

	'use strict';

	var JetEngineQuery = new Vue( {
		el: '#jet_query_form',
		template: '#jet-query-form',
		data: {
			generalSettings: {},
			restBase: JetEngineQueryConfig.rest_url,
			postTypes: JetEngineQueryConfig.post_types,
			rolesList: window.jet_query_component_users.roles,
			queryTypes: JetEngineQueryConfig.query_types,
			buttonLabel: JetEngineQueryConfig.edit_button_label,
			isEdit: JetEngineQueryConfig.item_id,
			helpLinks: JetEngineQueryConfig.help_links,
			typesComponents: JetEngineQueryConfig.types_components,
			hasClipboard: window.navigator.clipboard,
			permalinksType: JetEngineQueryConfig.permalinks_type,
			showDeleteDialog: false,
			saving: false,
			suggestions: [],
			updatingPreview: false,
			previewCount: 0,
			previewBody: null,
			isCopied: false,
			queryArgToDelete: -1,
			errors: {
				name: false,
			},
			errorNotices: [],
		},
		created: function() {
			this.updatePreview = _.debounce( this.updatePreviewCallback, 500 );
		},
		mounted: function() {

			var self = this;

			if ( JetEngineQueryConfig.item_id ) {

				wp.apiFetch( {
					method: 'get',
					path: JetEngineQueryConfig.api_path_get + JetEngineQueryConfig.item_id,
				} ).then( function( response ) {

					if ( response.success && response.data ) {

						for ( const property in response.data ) {
							self.$set( self.generalSettings, property, response.data[ property ] );
						}

						// Ensure arrays
						if ( ! self.generalSettings.api_access_role ) {
							self.$set( self.generalSettings, 'api_access_role', [] );
						}

						if ( ! self.generalSettings.api_schema ) {
							self.$set( self.generalSettings, 'api_schema', [ { arg: '', value: '' } ] );
						}

						self.updatePreview();

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
					console.log( e );
				} );

			} else {
				setTimeout( function() {
					self.$set( self.generalSettings, 'query_type', 'posts' );
					self.$set( self.generalSettings, 'cache_query', true );
				}, 1000 );
			}

		},
		methods: {
			endpointURL( addQueryArgs ) {
				let url = this.restBase + this.generalSettings.api_namespace + '/' + this.generalSettings.api_path + '/';

				if ( addQueryArgs ) {
					let queryArgsData = [];
					let queryArgsString = '';

					for ( var i = 0; i < this.generalSettings.api_schema.length; i++ ) {
						if ( this.generalSettings.api_schema[ i ].arg ) {
							queryArgsData.push( '' + this.generalSettings.api_schema[ i ].arg + '=' + this.generalSettings.api_schema[ i ].value );
						}
					}

					if ( queryArgsData.length ) {
						queryArgsString = queryArgsData.join( '&' );
					}

					if ( queryArgsString ) {
						url += '?' + queryArgsString;
					}

				}

				return url;
			},
			ensureAPIEndpointDefaults: function() {
				
				if ( ! this.generalSettings.api_namespace ) {
					this.$set( this.generalSettings, 'api_namespace', 'my' );
				}

				if ( ! this.generalSettings.api_path ) {
					let name = this.generalSettings.name || 'endpoint-' + Math.floor( Math.random() * 90 + 10 );
					this.$set( this.generalSettings, 'api_path', this.sanitizeSlug( name ) );
				}

				if ( ! this.generalSettings.api_access ) {
					this.$set( this.generalSettings, 'api_access', 'public' );
				}

				if ( ! this.generalSettings.api_schema ) {
					this.$set( this.generalSettings, 'api_schema', [ { arg: '', value: '' } ] );
				}

			},
			sanitizeSlug: function( slug ) {

				var regex = /\s+/g,
					slug  = slug.toLowerCase().replace( regex, '-' );

				// Replace accents
				slug = slug.normalize( 'NFD' ).replace( /[\u0300-\u036f]/g, "" );

				if ( 20 < slug.length ) {
					
					slug = slug.substr( 0, 20 );

					if ( '-' === slug.slice( -1 ) ) {
						slug = slug.slice( 0, -1 );
					}
				}

				return slug;

			},
			updateQueryArgs: function( index, prop, value ) {
				
				this.$set( this.generalSettings.api_schema[ index ], prop, value );

				// if ( value && index === this.generalSettings.api_schema.length - 1 ) {
				// 	this.generalSettings.api_schema.push( { arg: '', value: '' } );
				// }

			},
			deleteQueryArgument( index ) {
				this.generalSettings.api_schema.splice( index, 1 );
				this.queryArgToDelete = -1;

				if ( ! this.generalSettings.api_schema.length ) {
					this.generalSettings.api_schema.push( { arg: '', value: '' } );
				}

			},
			addQueryArgRow() {
				this.generalSettings.api_schema.push( { arg: '', value: '' } );
			},
			resetQueryArgDelete() {
				this.queryArgToDelete = -1;
			},
			copyToClipboard: function( text ) {

				if ( this.isCopied ) {
					return;
				}

				navigator.clipboard.writeText( text );

				this.isCopied = true;

				setTimeout( () => {
					this.isCopied = false;
				}, 500 );

			},
			hasQueryArgs() {

				if ( this.generalSettings.api_schema.length ) {
					for ( var i = 0; i < this.generalSettings.api_schema.length; i++ ) {
						if ( this.generalSettings.api_schema[ i ].arg ) {
							return true;
						}
					}
				}

				return false;
			},
			switchPreview: function( value ) {
				this.$set( this.generalSettings, 'show_preview', value );
				this.updatePreview();
			},
			updatePreviewCallback: function() {

				var self = this;

				if ( ! self.generalSettings.show_preview ) {
					return;
				}

				self.updatingPreview = true;

				var preview = {},
					query = {},
					dynamic_query = {};

				if ( self.generalSettings.preview_page || self.generalSettings.preview_page_title ) {
					preview.page = self.generalSettings.preview_page;
					preview.page_url = self.generalSettings.preview_page_title;
				}

				if ( self.generalSettings.preview_query_string ) {
					preview.query_string = self.generalSettings.preview_query_string;
				}

				if ( self.generalSettings.preview_query_count ) {
					preview.query_count = self.generalSettings.preview_query_count;
				}

				query         = self.generalSettings[ self.generalSettings.query_type ];
				dynamic_query = self.generalSettings[ '__dynamic_' + self.generalSettings.query_type ];

				wp.apiFetch( {
					method: 'post',
					path: JetEngineQueryConfig.api_path_update_preview,
					data: {
						preview: preview,
						query_id: JetEngineQueryConfig.item_id,
						query_type: self.generalSettings.query_type,
						query: query,
						dynamic_query: dynamic_query,
					}
				} ).then( function( response ) {

					if ( response.success ) {
						self.previewCount = response.count;
						self.previewBody  = response.data;
					}

					self.updatingPreview = false;
				} ).catch( function( response ) {
					self.updatingPreview = false;
					self.$CXNotice.add( {
						message: response.message,
						type: 'error',
						duration: 7000,
					} );

				} );

			},
			searchPreviewPage: function( value ) {

				var self = this;

				if ( ! value ) {
					self.$set( self.generalSettings, 'preview_page', null );
					self.$set( self.generalSettings, 'preview_page_title', '' );
					self.updatePreview();
					return;
				}

				if ( 2 > value.length ) {
					return;
				}

				wp.apiFetch( {
					method: 'get',
					path: JetEngineQueryConfig.api_path_search_preview + '?_s=' + value,
				} ).then( function( response ) {

					self.suggestions = response.data;

					if ( 'plain' !== self.permalinksType ) {
						self.suggestions.unshift( { id: 0, text: 'Use raw URL string', url: value } );
					}
				} ).catch( function( response ) {
					//self.errorNotices.push( response.message );

					self.$CXNotice.add( {
						message: response.message,
						type: 'error',
						duration: 7000,
					} );

				} );
			},
			applySuggestion: function( suggestion ) {
				if ( 0 !== suggestion.id ) {
					this.$set( this.generalSettings, 'preview_page_title', suggestion.text );
					this.$set( this.generalSettings, 'preview_page', suggestion.id );
				} else {
					this.$set( this.generalSettings, 'preview_page_title', suggestion.url );
					this.$set( this.generalSettings, 'preview_page', 0 );
				}
				
				this.suggestions = [];
				this.updatePreview();
			},
			ensureQueryType: function() {

				if ( this.generalSettings.query_type && ! this.generalSettings[ this.generalSettings.query_type ] ) {
					this.$set( this.generalSettings, this.generalSettings.query_type, {} );
				}

				if ( this.generalSettings.query_type && ! this.generalSettings[ '__dynamic_' + this.generalSettings.query_type ] ) {
					this.$set( this.generalSettings, '__dynamic_' + this.generalSettings.query_type, {} );
				}

			},
			handleFocus: function( where ) {

				if ( this.errors[ where ] ) {
					this.$set( this.errors, where, false );
					this.$CXNotice.close( where );
					//this.errorNotices.splice( 0, this.errorNotices.length );
				}

			},
			setDynamicQuery: function( prop, value ) {
				this.$set( this.generalSettings, prop, value );
				this.updatePreview();
			},
			isCacheable: function() {
				const queryType = this.generalSettings?.query_type;

				if ( ! queryType ) {
					return true;
				}

				return ! this.generalSettings[ queryType ]?.avoid_duplicates;
			},
			isAvoidDuplicates: function() {
				const queryType = this.generalSettings?.query_type;

				if ( ! queryType ) {
					return false;
				}

				return this.generalSettings[ queryType ]?.avoid_duplicates;
			},
			save: function() {

				var self      = this,
					hasErrors = false,
					path      = JetEngineQueryConfig.api_path_edit;

				if ( JetEngineQueryConfig.item_id ) {
					path += JetEngineQueryConfig.item_id;
				}

				for ( var errKey in this.errors ) {

					if ( ! self.generalSettings[ errKey ] ) {
						self.$set( this.errors, errKey, true );

						self.$CXNotice.add( {
							message: JetEngineQueryConfig.notices[ errKey ],
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

				wp.apiFetch( {
					method: 'post',
					path: path,
					data: {
						general_settings: self.generalSettings,
						meta_fields: self.metaFields,
					}
				} ).then( function( response ) {

					if ( response.success ) {
						if ( JetEngineQueryConfig.redirect ) {
							window.location = JetEngineQueryConfig.redirect.replace( /%id%/, response.item_id );
						} else {

							self.$CXNotice.add( {
								message: JetEngineQueryConfig.notices.success,
								type: 'success',
							} );

							self.saving = false;
						}
					} else {
						if ( response.notices.length ) {
							response.notices.forEach( function( notice ) {

								self.$CXNotice.add( {
									message: notice.message,
									type: 'error',
									duration: 7000,
								} );

							} );

							self.saving = false;
						}
					}
				} ).catch( function( response ) {
					//self.errorNotices.push( response.message );

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

})( jQuery, window.JetEngineQueryConfig );
