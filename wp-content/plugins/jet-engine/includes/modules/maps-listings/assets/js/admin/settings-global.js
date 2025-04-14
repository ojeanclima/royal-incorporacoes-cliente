(function( $, mapsSettings ) {

	'use strict';

	Vue.component( 'jet-engine-maps-settings', {
		template: '#jet_engine_maps_settings',
		data: function() {
			return {
				settings: mapsSettings.settings,
				nonce: mapsSettings._nonce,
				sources: mapsSettings.sources,
				customSources: mapsSettings.customSources,
				allFields: mapsSettings.fields,
				renderProviders: mapsSettings.renderProviders,
				fieldsProviders: mapsSettings.fieldsProviders,
				geoProviders: mapsSettings.geoProviders,
				showPopup: false,
				currentPopupProvider: 'jet-engine',
				currentPopupCustomSource: 'posts',
				currentPopupSource: '',
				currentPopupCustomFields: '',
				currentPopupFields: [],
				preloadWarnings: mapsSettings.preloadWarnings ?? '',
			};
		},
		methods: {
			updateSetting: function( value, setting ) {

				var self = this;

				self.$set( self.settings, setting, value );

				jQuery.ajax({
					url: window.ajaxurl,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'jet_engine_maps_save_settings',
						nonce: self.nonce,
						settings: self.settings,
					},
				}).done( function( response ) {
					if ( response.success ) {
						self.$CXNotice.add( {
							message: response.data.message,
							type: 'success',
							duration: 7000,
						} );

						self.preloadWarnings = response.data.additionalData.preloadWarnings ?? '';
					} else {
						self.$CXNotice.add( {
							message: response.data.message,
							type: 'error',
							duration: 15000,
						} );
					}
				} ).fail( function( jqXHR, textStatus, errorThrown ) {
					self.$CXNotice.add( {
						message: errorThrown,
						type: 'error',
						duration: 15000,
					} );
				} );
			},
			handlePopupOk: function() {

				if ( this.currentPopupFields.length ) {

					var preloadMeta = this.settings.preload_meta;

					if ( preloadMeta ) {
						preloadMeta = preloadMeta + ',' + this.currentPopupFields.join( '+' );
					} else {
						preloadMeta = this.currentPopupFields.join( '+' );
					}

				}

				let newFields = this.getPopupValue();

				if ( newFields ) {

					var preloadMeta = this.settings.preload_meta;

					if ( preloadMeta ) {
						preloadMeta = preloadMeta + ',' + newFields;
					} else {
						preloadMeta = newFields;
					}

					this.updateSetting( preloadMeta, 'preload_meta' );

				}

				this.handlePopupCancel();
			},
			getPopupValue: function() {

				let value = false;

				if ( 'jet-engine' === this.currentPopupProvider ) {
					if ( this.currentPopupFields.length ) {
						value = this.currentPopupFields.join( '+' );
					}
				} else {
					if ( this.currentPopupCustomFields ) {
						value = [ '_custom', this.currentPopupCustomSource, this.currentPopupCustomFields ].join( '::' );
					}
				}

				return value;

			},
			handlePopupCancel: function() {
				this.showPopup = false;
				this.currentPopupProvider = 'jet-engine';
				this.currentPopupSource = '';
				this.currentPopupCustomSource = 'posts';
				this.currentPopupFields = [];
				this.currentPopupCustomFields = '';
			},
			resetPopupFields: function() {
				this.currentPopupFields = [];
				this.$refs.current_popup_fields.setValues( [] );
				this.currentPopupCustomFields = '';
			},
			showPreloadWarnings: function() {
				return this.preloadWarnings?.length > 0;
			}
		}
	} );

})( jQuery, window.JetEngineMapsSettings );
