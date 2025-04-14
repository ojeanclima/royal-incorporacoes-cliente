(function( $ ) {

	'use strict';

	const SettingsEditor = Vue.extend( {
		template: '#jet-engine-component-edit-settings-tmpl',
		data() {
			return {
				componentID: false,
				mediaFrame: null,
				settings: {
					component_controls_list: [],
					component_style_controls_list: [],
				},
				currentMediaControl: null,
				componentControlsMode: 'content',
			};
		},
		mounted() {

			this.mediaFrame = window.wp.media.frames.file_frame = wp.media({
				multiple: false,
			});

			this.mediaFrame.on( 'select', () => {

				if ( null === this.currentMediaControl ) {
					return;
				}
				
				const attachment = this.mediaFrame.state().get( 'selection' ).toJSON();

				let imgURL;
				let imgThumb;

				imgURL = attachment[0].sizes.full.url;

				if ( attachment[0].sizes.thumbnail ) {
					imgThumb = attachment[0].sizes.thumbnail.url;
				} else {
					imgThumb = attachment[0].sizes.full.url;
				}
				
				this.setControlProp( 'component_controls_list', 'control_default_image', {
					id: attachment[0].id,
					url: imgURL,
					thumb: imgThumb,
				}, this.currentMediaControl );

			} );

		},
		methods: {
			unount() {
				this.mounted = false;
			},
			componentControlTypes() {
				return window.JetEngineComponentsData.component_control_types;
			},
			loadSettings() {

				$.ajax( {
					url: window.ajaxurl,
					type: 'GET',
					dataType: 'json',
					data: {
						action: 'jet_engine_get_component_settings',
						component_id: this.componentID,
						_nonce: window.JetListingsSettings._nonce,
					}
				} ).done( ( response ) => {

					if ( response.success ) {
						this.settings = response.data;
					}

				} );

			},
			hasControlDefaultImage( control ) {

				if ( ! control.control_default_image ) {
					return false;
				}

				if ( 'false' == control.control_default_image ) {
					return false;
				}

				if ( control.control_default_image.url ) {
					return true;
				}

				return false;

			},
			defaultImageSRC( defaultImageData ) {

				defaultImageData = defaultImageData || {};

				if ( defaultImageData.thumb ) {
					return defaultImageData.thumb;
				} else if ( defaultImageData.url ) {
					return defaultImageData.url;
				} else {
					return '';
				}

			},
			openMediaFrame( control, controlIndex ) {
				this.currentMediaControl = controlIndex;
				this.mediaFrame.open();
			},
			clearMediaControl( controlIndex ) {
				this.setControlProp( 'component_controls_list', 'control_default_image', false, controlIndex );
			},
			getRandomID() {
				return Math.floor( Math.random() * 8999 ) + 1000;
			},
			isCollapsed( object ) {
				if ( undefined === object.collapsed || true === object.collapsed ) {
					return true;
				} else {
					return false;
				}
			},
			addNewControl( where, defaultControl ) {

				var control = defaultControl;
				
				control.collapsed = false;
				control.id        = this.getRandomID();
				control._id       = 'uid' + control.id;

				this.settings[ where ].push( control );

			},
			saveSettings( openEditor, doneCallback ) {

				openEditor = openEditor || false;

				$.ajax( {
					url: window.ajaxurl,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'jet_engine_set_component_settings',
						component_id: this.componentID,
						settings: this.settings,
						_nonce: window.JetListingsSettings._nonce,
					}
				} ).done( function( response ) {

					if ( response.success && openEditor && response.data ) {
						window.location = response.data;
					}

					doneCallback();

				} );

			},
			cloneControl( index, where ) {

				var newControl = JSON.parse( JSON.stringify( this.settings[ where ][ index ] ) );

				newControl.control_label = newControl.control_label + ' (Copy)';
				newControl.control_name  = newControl.control_name + '_copy';
				newControl.id            = this.getRandomID();
				newControl._id           = 'uid' + newControl.id;

				this.settings[ where ].splice( index + 1, 0, newControl );

			},
			deleteControl( index, where ) {
				this.settings[ where ].splice( index, 1 );
			},
			preSetControlName( index, where ) {

				if ( ! this.settings[ where ][ index ].control_label ) {
					return;
				}

				let regex = /\s+/g;
				let name = this.settings[ where ][ index ].control_label.toLowerCase().replace( regex, '_' );

				this.setControlProp( where, 'control_name', name, index );

			},
			setControlProp( where, prop, value, index ) {
				var control = JSON.parse( JSON.stringify( this.settings[ where ][ index ] ) );
				control[ prop ] = value;
				this.settings[ where ].splice( index, 1, control );
			},
			switchControls( settings ) {

				if ( ! this.$refs.popup ) {
					return;
				}

				settings = settings || this.settings;

				let $allControls = this.$refs.popup.querySelectorAll( ".jet-template-listing" );

				$allControls.forEach( ( $item ) => {

					$item.style.display = 'none';

					if ( $item.classList.contains( "jet-template-" + settings.listing_source ) ) {
						$item.style.display = 'block';
					}

				} );

			},
		}
	} );

	class JetEngineComponents {

		namespace = 'JetEngineComponents';
		currentSettingsComponent = null;

		constructor() {
			
			window.JetPlugins.hooks.addFilter( 'jetEngine.listing.popup', this.namespace, this.setPopup );

			const $document = $( document );

			$document.on( 'click', '.jet-engine-component-edit-settings', ( e ) =>  {

				e.preventDefault();

				if ( e.target.dataset.componentId ) {
					this.openComponentSettings( e.target.dataset.componentId );
				}

			} );

			$document.on( 'click', '.jet-engine-component-save', ( e ) => {
				this.saveSettings( e.target.classList.contains( 'open-editor' ), e.target );
			});

			this.$settingsContainer = document.getElementById( 'jet_engine_component_settings_content' );
			this.$settingsPopup     = document.getElementById( 'jet_engine_component_settings_popup' );

		}

		saveSettings( openEditor, button ) {
			if ( this.currentSettingsComponent ) {
				button.setAttribute( 'disabled', true );
				button.style.opacity = '0.6';
				this.currentSettingsComponent.saveSettings( openEditor, () => {
					button.removeAttribute( 'disabled' );
					button.style.opacity = '1';
				} );
			}
		}

		openComponentSettings( componentId ) {
			
			this.$settingsPopup.classList.add( 'jet-listings-popup-active' );

			if ( this.currentSettingsComponent ) {
				this.currentSettingsComponent.$el.replaceWith( this.$settingsContainer );
			}

			this.currentSettingsComponent = new SettingsEditor( {
				data() {
					return {
						componentID: componentId,
						mediaFrame: null,
						settings: {
							component_controls_list: [],
							component_style_controls_list: [],
						},
						currentMediaControl: null,
						componentControlsMode: 'content',
						mounted: true,
					};
				}
			} ).$mount( this.$settingsContainer );

			this.currentSettingsComponent.loadSettings();

		}

		setPopup( $popup, triggerEvent, defaultArgs ) {

			if ( triggerEvent && triggerEvent.target.classList.contains( 'is-new-component' ) ) {
				$popup = $( '.jet-listings-popup.is-component-popup' );
			}

			return $popup;

		}
	}

	new JetEngineComponents();



})( jQuery );
