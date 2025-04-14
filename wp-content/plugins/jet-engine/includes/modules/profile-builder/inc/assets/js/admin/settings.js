(function( $, JetEngineProfileBuilder ) {

	'use strict';

	Vue.filter( 'nonAdmins', function ( roles ) {
		var result = roles.filter( function( role ) {
			return "administrator" !== role.value && "jet-engine-guest" !== role.value;
		} );

		return result;
	});

	Vue.component( 'jet-profile-new-template', {
		name: 'jet-profile-new-template',
		template: '#jet-profile-builder-new-template',
		data: function() {
			return {
				showPopup: false,
				templateName: '',
				templateType: '',
				templateView: '',
				listingSource: '',
				defaultView: '',
				nameError: false,
				creating: false,
			};
		},
		created() {
			this.listingSource = this.templateSources()[0].value;
			this.defaultView   = this.templateViews()[0].value;
			this.templateType  = this.listingSource;
			this.templateView  = this.defaultView;
		},
		computed: {
			hasNameError() {
				return ! this.templateName && this.nameError
			},
		},
		methods: {
			templateSources() {
				return this.arrayFromObject( window.JetEngineProfileBuilder.template_sources );
			},
			templateViews() {
				return this.arrayFromObject( window.JetEngineProfileBuilder.listing_views );
			},
			arrayFromObject( object ) {
				const result = [];
				
				for ( const value in object ) {
					result.push( {
						value: value,
						label: object[ value ]
					} );
				}

				return result;
			},
			closePopup() {
				this.showPopup = false;
				this.nameError = false;
				this.templateName = '';
				this.templateType = this.listingSource;
				this.templateView = this.defaultView;
			},
			createTemplate() {

				if ( ! this.templateName ) {
					this.nameError = true;
					return;
				}

				this.creating = true;

				jQuery.ajax({
					url: window.ajaxurl,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'jet_engine_create_profile_template',
						template_name: this.templateName,
						template_type: this.templateType,
						template_view: this.templateView,
						_nonce: JetEngineProfileBuilder._nonce,
					},
				}).done( ( response ) => {

					this.creating = false;

					if ( response.success ) {

						this.$emit( 'on-create', { 
							value: response.data.template_id, 
							label: this.templateName + ' (' + window.JetEngineProfileBuilder.template_sources[ this.templateType ] + ')'
						} );

						window.open( response.data.template_url, '_blank' ).focus();
						this.closePopup();
					} else {
						this.$CXNotice.add( {
							message: response.data.message,
							type: 'error',
							duration: 7000,
						} );
					}

				} ).fail( ( e, textStatus ) => {

					this.creating = false;

					this.$CXNotice.add( {
						message: e.statusText,
						type: 'error',
						duration: 7000,
					} );

				} );
				
			}
		}
	} );

	Vue.component( 'jet-profile-macros', {
		name: 'jet-profile-macros',
		template: '#jet-profile-builder-macros',
		directives: { clickOutside: window.JetVueUIClickOutside },
		data: function() {
			return {
				isActive: false,
				macrosList: window.JetEngineProfileBuilder.profile_builder_macros,
			};
		},
		props: {
			activeTab: {
				type: String,
				default: ''
			},
		},
		methods: {
			switchIsActive: function() {
				this.isActive = !this.isActive;
			},
			addMacro: function( macro ) {
				this.$emit( 'add-macro', macro );
				this.isActive = false;
			},
			isMacroAllowed: function( macroArgs ) {
				return macroArgs.allowed_tabs.includes( this.activeTab );
			},
			onClickOutside: function() {
				this.isActive = false;
			},
		}
	} );

	new Vue( {
		el: '#jet_engine_profile_builder',
		template: '#jet-profile-builder',
		data: {
			settings: JetEngineProfileBuilder.settings,
			pagesList: JetEngineProfileBuilder.pages,
			notLoggedActions: JetEngineProfileBuilder.not_logged_in_actions,
			rewriteOptions: JetEngineProfileBuilder.rewrite_options,
			visibilityOptions: JetEngineProfileBuilder.visibility_options,
			userRoles: JetEngineProfileBuilder.user_roles,
			postTypes: JetEngineProfileBuilder.post_types,
			userPageImageFields: JetEngineProfileBuilder.user_page_image_fields,
			saving: false,
			activeTab: '',
		},
		mounted: function() {

			this.$el.className = 'is-mounted';

			if ( ! this.settings.account_page_structure ) {
				this.$set( this.settings, 'account_page_structure', [
					{
						title: 'Main',
						slug: 'main',
						template: '',
						collapsed: false,
						id: this.getRandomID(),
					}
				] );
			}

			if ( ! this.settings.user_page_structure ) {
				this.$set( this.settings, 'user_page_structure', [
					{
						title: 'Main',
						slug: 'main',
						template: '',
						visibility: 'all',
						collapsed: false,
						id: this.getRandomID(),
					}
				] );
			}

			this.activeTab = this.$refs.settingsTabs.activeTab;

			this.$refs.settingsTabs.$on( 'input', ( activeTab ) => this.activeTab = activeTab );

		},
		watch: {
			settings: {
				handler: function( newSettings, oldSettings ) {
					var self = this;

					Vue.nextTick( function() {
						self.$refs.settingsTabs.updateState();
					} );
				},
				deep: true,
			}
		},
		computed: {
			userRolesForPages: function() {

				var roles = [],
					hasAdmin = false;

				for ( var i = 0; i < this.userRoles.length; i++) {

					if ( 'administrator' === this.userRoles[ i ].value ) {
						hasAdmin = true;
					}

				}

				if ( ! hasAdmin ) {
					roles.push( {
						value: 'administrator',
						label: 'Administrator',
					} );
				}

				for ( var i = 0; i < this.userRoles.length; i++) {

					if ( 'jet-engine-guest' !== this.userRoles[ i ].value ) {
						roles.push( this.userRoles[ i ] );
					}

				}

				return roles;

			},
			notAccessibleActions: function() {
				return this.notLoggedActions.filter( function( item ) {
					return 'login_redirect' !== item.value;
				} );
			},
		},
		methods: {
			setCreatedTemplate( where, prop, value, ref ) {

				this.$set( where, prop, [ value.value ] );
				
				if ( ! ref ) {
					ref = prop;
				}

				if ( this.$refs[ ref ][0] ) {
					this.$refs[ ref ][0].selectedOptions = [ value ];
				} else {
					this.$refs[ ref ].selectedOptions = [ value ];
				}

				

				this.saveSettings();
			},
			getRandomID: function() {
				return Math.floor( Math.random() * 8999 ) + 1000;
			},
			stringifyRoles: function( roles, placeholder ) {

				placeholder = placeholder || false;

				if ( ! roles || ! roles.length ) {
					if ( placeholder ) {
						return 'all users';
					} else {
						return '';
					}
				}

				return roles.join( ', ' );

			},
			stringifyLimit: function( limit ) {

				if ( ! limit || 0 == limit ) {
					limit = '∞';
				}

				return '' + limit;

			},
			preSetSlug: function( index, setting ) {

				var pages   = this.settings[ setting ],
					page    = pages[ index ];

				if ( ! page.slug && page.title ) {
					var regex = /\s+/g;
					page.slug = page.title.toLowerCase().replace( regex, '-' );
					pages.splice( index, 1, page );
					this.$set( this.settings, setting, pages );
				}

			},
			addNewRepeaterItem: function( setting, item ) {
				var items = this.settings[ setting ];

				item.id = this.getRandomID();

				items.push( item );

				this.$set( this.settings, setting, items );
			},
			addNewPage: function( setting ) {

				var pages   = this.settings[ setting ],
					newPage = {
						title: '',
						slug: '',
						template: '',
						collapsed: false,
						id: this.getRandomID(),
					};

				pages.push( newPage );

				this.$set( this.settings, setting, pages );

			},
			buildQuery: function( params ) {
				return Object.keys( params ).map(function( key ) {
					return key + '=' + params[ key ];
				}).join( '&' );
			},
			getPosts: function( query, ids ) {

				if ( ids.length ) {
					ids = ids.join( ',' );
				}

				return wp.apiFetch( {
					method: 'get',
					path: JetEngineProfileBuilder.search_api + '?' + this.buildQuery( {
						query: query,
						ids: ids,
						post_type: JetEngineProfileBuilder.search_in.join( ',' ),
						query_context: 'profile-builder',
					} )
				} );
			},
			cloneItem: function( index, setting, keys ) {

				var items   = this.settings[ setting ],
					item    = items[ index ],
					newItem = {};

				for ( var i = 0; i < keys.length; i++ ) {
					newItem[ keys[ i ] ] = item[ keys[ i ] ];
				};

				newItem.id = this.getRandomID();

				newItem = JSON.parse( JSON.stringify( newItem ) );

				items.push( newItem );

				this.$set( this.settings, setting, items );

			},
			clonePage: function( index, setting ) {
				var pages   = this.settings[ setting ],
					page    = pages[ index ],
					newPage = {
						title: page.title + ' (Copy)',
						slug: page.slug + '-copy',
						template: page.template,
						id: this.getRandomID(),
					};

				pages.push( newPage );

				this.$set( this.settings, setting, pages );

			},
			deleteItem: function( index, setting ) {
				var items = this.settings[ setting ];
				items.splice( index, 1 );
				this.$set( this.settings, setting, items );
			},
			deletePage: function( index, setting ) {
				var pages = this.settings[ setting ];
				pages.splice( index, 1 );
				this.$set( this.settings, setting, pages );
			},
			setPageProp: function( index, key, value, setting ) {
				var pages = this.settings[ setting ],
					page  = pages[ index ];

				page[ key ] = value;

				pages.splice( index, 1, page );
				this.$set( this.settings, setting, pages );
			},
			isCollapsed: function( object ) {

				if ( undefined === object.collapsed || true === object.collapsed ) {
					return true;
				} else {
					return false;
				}

			},
			addMacroToField: function( macro, setting ) {

				if ( this.settings[ setting ] ) {
					this.settings[ setting ] += ' ' + macro;
				} else {
					this.$set( this.settings, setting, macro );
				}

			},
			saveSettings: function() {

				var self = this;

				self.saving = true;

				jQuery.ajax({
					url: window.ajaxurl,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'jet_engine_save_settings',
						settings: self.settings,
						_nonce: JetEngineProfileBuilder._nonce,
					},
				}).done( function( response ) {

					self.saving = false;

					if ( response.success ) {
						self.$CXNotice.add( {
							message: 'Settings Saved!',
							type: 'success',
							duration: 7000,
						} );
					} else {
						self.$CXNotice.add( {
							message: response.data.message,
							type: 'error',
							duration: 7000,
						} );
					}

				} ).fail( function( e, textStatus ) {
					self.saving = false;
					self.$CXNotice.add( {
						message: e.statusText,
						type: 'error',
						duration: 7000,
					} );
				} );

			},
		}
	} );

})( jQuery, window.JetEngineProfileBuilder );
