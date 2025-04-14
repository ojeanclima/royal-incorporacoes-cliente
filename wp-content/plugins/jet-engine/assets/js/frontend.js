( function( $ ) {

	"use strict";

	var JetEngineRegisteredStores = window.JetEngineRegisteredStores || {};
	var JetEngineStores           = window.JetEngineStores || {};

	var JetEngine = {

		currentMonth: null,
		currentRequest: {},
		activeCalendarDay: null,
		lazyLoading: false,
		addedScripts: [],
		addedStyles: [],
		addedPostCSS: [],
		assetsPromises: [],

		initDone: false,

		commonInit: function() {

			JetEngine.commonEvents();

			$( window ).on( 'jet-popup/render-content/ajax/success', JetEngine.initStores );

			window.JetPlugins.hooks.addFilter(
				'jet-popup.show-popup.data',
				'JetEngine.popupData',
				( popupData, $popup, $triggeredBy ) => {

					if ( ! $triggeredBy ) {
						return popupData;
					}

					if ( $triggeredBy.data( 'popupIsJetEngine' ) ) {
						popupData = JetEngine.prepareJetPopup( popupData, { 'is-jet-engine': true }, $triggeredBy );
					}

					return popupData;
				}
			);

			JetEngine.initStores();
			JetEngine.customUrlActions.init();

		},

		commonEvents: function( $scope ) {
			$scope = $scope || $( document );

			$scope
				.on( 'jet-filter-content-rendered', JetEngine.calendarCache.clear )
				.on( 'change.JetEngine', '.jet-calendar-caption__date-select', JetEngine.selectCalendarMonth )
				.on( 'click.JetEngine', '.jet-calendar-nav__link', JetEngine.switchCalendarMonth )
				.on( 'click.JetEngine', '.jet-calendar-week__day-mobile-overlay', JetEngine.showCalendarEvent )
				.on( 'click.JetEngine', '.jet-listing-dynamic-link__link[data-delete-link="1"]', JetEngine.showConfirmDeleteDialog )
				.on( 'jet-filter-content-rendered', JetEngine.maybeReinitSlider )
				.on( 'click.JetEngine', '.jet-add-to-store', JetEngine.addToStore )
				.on( 'click.JetEngine', '.jet-remove-from-store', JetEngine.removeFromStore )
				.on( 'click.JetEngine', '.jet-engine-listing-overlay-wrap:not([data-url*="event=hover"])', JetEngine.handleListingItemClick )
				.on( 'jet-filter-content-rendered', JetEngine.filtersCompatibility )
				.on( 'click.JetEngine', '.jet-container[data-url]', JetEngine.handleContainerURL )
				.on( 'change.JetEngine', '.jet-listing-dynamic-link .qty', JetEngine.handleProductQuantityChange );
		},

		handleProductQuantityChange: function ( event ) {
			event.preventDefault();
			event.stopPropagation();

			const $this = $( this );

			$this.closest( ".jet-listing-dynamic-link" ).find( ".jet-woo-add-to-cart" ).data( "quantity", $this.val() ).attr( "data-quantity", $this.val() );
		},

		handleContainerURL: function() {
			var $this  = $( this ),
				url    = $this.data( 'url' ),
				target = $this.data( 'target' );

			if ( ! target ) {
				window.location = url;
			} else {
				window.open( url, '_blank' ).focus();
			}

		},

		filtersCompatibility: function( event, $provider, filtersInstance, providerType ) {

			if ( 'jet-engine' !== providerType ) {
				return;
			}

			/*
			No need anymore to manually re-init the block.
			JetSmartFilters automatically re-init blocks.

			var $blocksListing = $provider.closest( '.jet-listing-grid--blocks' );

			if ( $blocksListing.length ) {
				JetEngine.widgetListingGrid( $blocksListing );
			}
			*/

			if ( window.JetPopupFrontend && window.JetPopupFrontend.initAttachedPopups ) {
				window.JetPopupFrontend.initAttachedPopups( $provider );
			}
		},

		init: function() {

			var widgets = {
				'jet-listing-dynamic-field.default' : JetEngine.widgetDynamicField,
				'jet-listing-grid.default': JetEngine.widgetListingGrid,
			};

			$.each( widgets, function( widget, callback ) {
				window.elementorFrontend.hooks.addAction( 'frontend/element_ready/' + widget, callback );
			});

			// Re-init sliders in nested tabs
			window.elementorFrontend.elements.$window.on(
				'elementor/nested-tabs/activate',
				( event, content ) => {
					const $content = $( content );

					setTimeout( () => {
						JetEngine.maybeReinitSlider( event, $content );
						JetEngine.widgetDynamicField( $content );
					} );
				}
			);

			window.elementorFrontend.hooks.addFilter(
				'jet-popup/widget-extensions/popup-data',
				JetEngine.prepareJetPopup
			);

			window.JetPlugins.hooks.addFilter(
				'jet-popup.show-popup.data',
				'JetEngine.popupData',
				( popupData, $popup, $triggeredBy ) => {

					if ( ! $triggeredBy ) {
						return popupData;
					}

					if ( $triggeredBy.data( 'popupIsJetEngine' ) ) {
						popupData = JetEngine.prepareJetPopup( popupData, { 'is-jet-engine': true }, $triggeredBy );
					}

					return popupData;
				}
			);

			JetEngine.updateAddedStyles();
		},

		initBricks: function( $scope ) {

			if ( window.bricksIsFrontend ) {
				return;
			}

			$scope = $scope || $( 'body' );
			JetEngine.initBlocks( $scope );

		},

		initBlocks: function( $scope ) {

			$scope = $scope || $( 'body' );

			window.JetPlugins.init( $scope, [
				{
					block: 'jet-engine/listing-grid',
					callback: JetEngine.widgetListingGrid
				},
				{
					block: 'jet-engine/dynamic-field',
					callback: JetEngine.widgetDynamicField
				}
			] );
		},

		initFrontStores: function( $scope ) {

			$scope = $scope || $( 'body' );

			$( '.jet-add-to-store.is-front-store', $scope ).each( function() {

				var $this = $( this ),
					args  = $this.data( 'args' ),
					store = JetEngineStores[ args.store.type ],
					count = 0;

				args = JetEngine.ensureJSON( args );

				if ( ! store ) {
					return;
				}

				if ( store.inStore( args.store.slug, '' + args.post_id ) ) {
					JetEngine.switchDataStoreStatus( $this );
				}

			} );

			$( '.jet-remove-from-store.is-front-store', $scope ).each( function() {

				var $this = $( this ),
					args  = $this.data( 'args' ),
					store = JetEngineStores[ args.store.type ],
					count = 0;

				args = JetEngine.ensureJSON( args );

				if ( ! store ) {
					return;
				}

				if ( ! store.inStore( args.store.slug, '' + args.post_id ) ) {
					$this.addClass( 'is-hidden' );
				} else {
					$this.removeClass( 'is-hidden' );
				}

			} );

		},

		initStores: function() {

			JetEngine.initFrontStores();

			$.each( JetEngineRegisteredStores, function( storeSlug, storeType ) {

				var store = JetEngineStores[ storeType ],
					storeData = null,
					count = 0;

				if ( ! store ) {
					return;
				}

				storeData = store.getStore( storeSlug );

				if ( storeData && storeData.length ) {
					count = storeData.length;
				}

				$( 'span.jet-engine-data-store-count[data-store="' + storeSlug + '"]' ).text( count );

			} );

			JetEngine.loadFrontStoresItems();

		},

		loadFrontStoresItems: function( $scope ) {

			$scope = $scope || $( 'body' );

			$( '.jet-listing-not-found.jet-listing-grid__items', $scope ).each( function() {

				var $this   = $( this ),
					nav     = $this.data( 'nav' ),
					isStore = $this.data( 'is-store-listing' ),
					query   = nav.query || {};

				nav = JetEngine.ensureJSON( nav );

				if ( query && query.post__in && query.post__in.length && 0 >= query.post__in.indexOf( 'is-front' ) ) {

					var storeType  = query.post__in[1],
						storeSlug  = query.post__in[2],
						store      = JetEngineStores[ storeType ],
						posts      = [],
						$container = $this.closest( '.jet-listing-grid' );

					if ( ! store ) {
						return;
					}

					//Context Gutenberg
					if ( ! $container.length ) {
						$container = $this.closest( '.jet-listing-grid--blocks' );
					}

					// Context Bricks
					if ( ! $container.length ) {
						$container = $this.closest( '.brxe-jet-engine-listing-grid' )
					}

					posts = store.getStore( storeSlug );

					if ( ! posts.length ) {
						return;
					}

					query.post__in = posts;
					query.is_front_store = true;

					JetEngine.ajaxGetListing( {
						handler: 'get_listing',
						container: $container,
						masonry: false,
						slider: false,
						append: false,
						query: query,
						widgetSettings: nav.widget_settings,
					}, function( response ) {
						JetEngine.widgetListingGrid( $container );
					} );

				} else if ( isStore ) {
					$( document ).trigger( 'jet-listing-grid-init-store', $this );
				}

			} );
		},

		removeFromStore: function( event ) {

			event.preventDefault();
			event.stopPropagation();

			var $this = $( this ),
				args  = $this.data( 'args' ),
				isDataStoreBtn = $this.hasClass( 'jet-data-store-link' );

			args = JetEngine.ensureJSON( args );

			if ( args.store.is_front ) {

				var store = JetEngineStores[ args.store.type ],
					count = 0;

				if ( ! store ) {
					return;
				}

				if ( ! store.inStore( args.store.slug, '' + args.post_id ) ) {
					var storePosts = store.getStore( args.store.slug );
					count = storePosts.length;
				} else {
					count = store.remove( args.store.slug, args.post_id );
				}

				$( '.jet-add-to-store[data-store="' + args.store.slug + '"][data-post="' + args.post_id + '"]' ).each( function() {
					JetEngine.switchDataStoreStatus( $( this ), true );
				} );

				$( '.jet-data-store-link.jet-remove-from-store[data-store="' + args.store.slug + '"][data-post="' + args.post_id + '"]' ).each( function() {
					JetEngine.switchDataStoreStatus( $( this ), true );
				} );

				$( 'span.jet-engine-data-store-count[data-store="' + args.store.slug + '"]' ).text( count );

				if ( args.remove_from_listing ) {
					$this.closest( '.jet-listing-dynamic-post-' + args.post_id ).remove();
				}

				JetEngine.dataStoreSyncListings( args );

				$( document ).trigger( 'jet-engine-data-stores-on-remove', args );

				return;

			}

			if ( $this.hasClass( 'jet-store-processing' ) ) {
				return;
			}

			$this.css( 'opacity', 0.3 );
			$this.addClass( 'jet-store-processing' );

			$.ajax({
				url: JetEngineSettings.ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'jet_engine_remove_from_store_' + args.store.slug,
					store: args.store.slug,
					post_id: args.post_id,
				},
			}).done( function( response ) {

				$this.css( 'opacity', 1 );
				$this.removeClass( 'jet-store-processing' );

				if ( response.success ) {

					if ( ! isDataStoreBtn ) {
						$this.addClass( 'is-hidden' );
					}

					$( '.jet-add-to-store[data-store="' + args.store.slug + '"][data-post="' + args.post_id + '"]' ).each( function() {
						JetEngine.switchDataStoreStatus( $( this ), true );
					} );

					$( '.jet-data-store-link.jet-remove-from-store[data-store="' + args.store.slug + '"][data-post="' + args.post_id + '"]' ).each( function() {
						JetEngine.switchDataStoreStatus( $( this ), true );
					} );

					JetEngine.dataStoreSyncListings( args );

					if ( args.remove_from_listing ) {
						$this.closest( '.jet-listing-grid__item[data-post="' + args.post_id + '"]' ).remove();
					}

					if ( response.data.fragments ) {
						$.each( response.data.fragments, function( selector, value ) {
							$( selector ).html( value );
						} );
					}

					$( document ).trigger( 'jet-engine-data-stores-on-remove', args );

				} else {
					alert( response.data.message );
				}

				return response;

			} ).done( function( response ) {

				if ( args.remove_from_listing ) {
					$this.closest( '.jet-listing-grid__item' ).remove();
				}

				if ( response.success ) {
					$( 'span.jet-engine-data-store-count[data-store="' + args.store.slug + '"]' ).text( response.data.count );
				}

			} ).fail( function( jqXHR, textStatus, errorThrown ) {
				$this.css( 'opacity', 1 );
				$this.removeClass( 'jet-store-processing' );
				alert( errorThrown );
			} );

		},

		triggerPopup: function( popupID, isJetEngine, postID ) {

			if ( ! popupID ) {
				return;
			}

			var popupData = {
				popupId: 'jet-popup-' + popupID,
			};

			if ( isJetEngine ) {
				popupData.isJetEngine = true;
				popupData.postId      = postID;
			}

			$( window ).trigger( {
				type: 'jet-popup-open-trigger',
				popupData: popupData
			} );

		},

		dataStoreSyncListings: function( args ) {
			if ( ! args.synch_id || typeof args.synch_id !== 'string' ) {
				return;
			}

			const ids = args.synch_id.split( /[\s,]+/ ).map( ( id ) => id.replace( /\s/, '' ) ).filter( ( id ) => !! id );

			ids.forEach( function ( id ) {
				let $container = $( '#' + id ),
				$elemContainer = $container.find( '> .elementor-widget-container' );

				if ( ! $container.length ) {
					return;
				}

				let $items         = $container.find( '.jet-listing-grid__items' ),
				    posts          = [],
				    nav            = $items.data( 'nav' ) || {},
				    query          = nav.query || {},
					postID         = window.elementorFrontendConfig?.post?.id || 0;

				nav = JetEngine.ensureJSON( nav );

				// Context Bricks
				if ( $container.hasClass( 'brxe-jet-engine-listing-grid' ) ) {
					postID = window.bricksData.postId;
				}
				
				// Context Gutenberg
				if ( $container.hasClass( 'jet-listing-grid--blocks' )) {
					postID = JetEngineSettings.post_id;
				}

				if ( args?.store?.is_front && Object.keys( query ).length ) {
					let store = JetEngineStores[ args.store.type ];

					posts = store.getStore( args.store.slug );

					if ( ! posts.length ) {
						posts = [ 'is-front', args.store.type, args.store.slug ];
					}

					query.post__in = posts;
					query.is_front_store = true;
				}

				let options = {
					handler: 'get_listing',
					container: $elemContainer.length ? $elemContainer : $container,
					masonry: false,
					slider: false,
					append: false,
					query: query,
					widgetSettings: nav.widget_settings,
					postID: postID,
					elementID: $container.data( 'id' ),
				};

				JetEngine.ajaxGetListing( options, function( response ) {
					JetEngine.widgetListingGrid( $container );
				} );
			} );
		},

		addToStore: function( event ) {

			event.preventDefault();
			event.stopPropagation();

			var $this = $( this ),
				args  = $this.data( 'args' );

			args = JetEngine.ensureJSON( args );

			if ( $this.hasClass( 'in-store' ) ) {
				if ( args.popup ) {
					JetEngine.triggerPopup( args.popup, args.isJetEngine, args.post_id );
				} else if ( '_blank' === $this.attr( 'target' ) ) {
					window.open( $this.attr( 'href' ) );
				} else {
					window.location = $this.attr( 'href' );
				}
				return;
			}

			if ( args.store.is_front ) {

				var store = JetEngineStores[ args.store.type ],
					count = 0;

				if ( ! store ) {
					return;
				}

				if ( store.inStore( args.store.slug, '' + args.post_id ) ) {
					var storePosts = store.getStore( args.store.slug );
					count = storePosts.length;
				} else {

					count = store.addToStore( args.store.slug, args.post_id, args.store.size );

					if ( false === count ) {
						return;
					}

				}

				if ( args.popup ) {
					JetEngine.triggerPopup( args.popup, args.isJetEngine, args.post_id );
				}

				JetEngine.switchDataStoreStatus( $this );
				$( 'span.jet-engine-data-store-count[data-store="' + args.store.slug + '"]' ).text( count );
				$( '.jet-remove-from-store[data-store="' + args.store.slug + '"][data-post="' + args.post_id + '"]' ).removeClass( 'is-hidden' );

				JetEngine.dataStoreSyncListings( args );

				$( document ).trigger( 'jet-engine-data-stores-on-add', args );

				return;
			}

			if ( $this.hasClass( 'jet-store-processing' ) ) {
				return;
			}

			$this.css( 'opacity', 0.3 );
			$this.addClass( 'jet-store-processing' );

			$( document ).trigger( 'jet-engine-on-add-to-store', [ $this, args ] );

			$.ajax({
				url: JetEngineSettings.ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'jet_engine_add_to_store_' + args.store.slug,
					store: args.store.slug,
					post_id: args.post_id,
				},
			}).done( function( response ) {

				$this.css( 'opacity', 1 );
				$this.removeClass( 'jet-store-processing' );

				if ( response.success ) {

					JetEngine.switchDataStoreStatus( $this );
					$( '.jet-remove-from-store[data-store="' + args.store.slug + '"][data-post="' + args.post_id + '"]' ).removeClass( 'is-hidden' );

					if ( response.data.fragments ) {
						$.each( response.data.fragments, function( selector, value ) {
							$( selector ).html( value );
						} );
					}

					JetEngine.dataStoreSyncListings( args );

					if ( args.popup ) {
						JetEngine.triggerPopup( args.popup, args.isJetEngine, args.post_id );
					}

				} else {
					alert( response.data.message );
				}

				$( document ).trigger( 'jet-engine-data-stores-on-add', args );

				return response;

			} ).done( function( response ) {

				if ( response.success ) {
					$( 'span.jet-engine-data-store-count[data-store="' + args.store.slug + '"]' ).text( response.data.count );
				}

			} ).fail( function( jqXHR, textStatus, errorThrown ) {
				$this.css( 'opacity', 1 );
				$this.removeClass( 'jet-store-processing' );
				alert( errorThrown );
			} );

		},

		switchDataStoreStatus: function( $item, toInitial ) {

			var isDataStoreLink = $item.hasClass( 'jet-data-store-link' ),
				$label = $item.find( '.jet-listing-dynamic-link__label, .jet-data-store-link__label' ),
				$icon  = $item.find( '.jet-listing-dynamic-link__icon, .jet-data-store-link__icon' ),
				args   = $item.data( 'args' ),
				replaceLabel,
				replaceURL,
				replaceIcon;

			args = JetEngine.ensureJSON( args );

			toInitial = toInitial || false;

			if ( isDataStoreLink ) {

				switch ( args.action_after_added ) {
					case 'remove_from_store':

						if ( toInitial ) {
							$item.addClass( 'jet-add-to-store' );
							$item.removeClass( 'jet-remove-from-store' );

							$item.removeClass( 'in-store' );
						} else {
							$item.addClass( 'jet-remove-from-store' );
							$item.removeClass( 'jet-add-to-store' );

							$item.addClass( 'in-store' );

						}

						break;

					case 'hide':

						if ( toInitial ) {
							$item.removeClass( 'is-hidden' );
						} else {
							$item.addClass( 'is-hidden' );
						}

						return;
				}

			}

			if ( toInitial ) {
				replaceLabel = args.label;
				replaceIcon  = args.icon;
				replaceURL   = '#';
			} else {
				replaceLabel = args.added_label;
				replaceIcon  = args.added_icon;
				replaceURL   = args.added_url;
			}

			if ( $label.length ) {
				$label.replaceWith( replaceLabel );
			} else {
				$item.append( replaceLabel );
			}

			if ( $icon.length ) {
				$icon.replaceWith( replaceIcon );
			} else {
				$item.prepend( replaceIcon );
			}

			if ( isDataStoreLink && 'remove_from_store' === args.action_after_added ) {
				return;
			}

			$item.attr( 'href', replaceURL );

			if ( toInitial ) {
				$item.removeClass( 'in-store' );
			} else if ( ! $item.hasClass( 'in-store' ) ) {
				$item.addClass( 'in-store' );
			}


		},

		showConfirmDeleteDialog: function( event ) {
			event.preventDefault();
			event.stopPropagation();

			var $this = $( this );

			if ( window.confirm( $this.data( 'delete-message' ) ) ) {
				window.location = $this.attr( 'href' );
			}

		},

		handleListingItemClick: function( event ) {

			var url    = $( this ).data( 'url' ),
				target = $( this ).data( 'target' ) || false;

			if ( url ) {

				event.preventDefault();

				if ( window.elementorFrontend && window.elementorFrontend.isEditMode() ) {
					return;
				}

				if ( -1 !== url.indexOf( '#jet-engine-action' ) ) {

					JetEngine.customUrlActions.runAction( url );

				} else {

					if ( '_blank' === target ) {
						window.open( url );
						return;
					}

					window.location = url;
				}
			}

		},

		customUrlActions: {
			selectorOnClick: 'a[href^="#jet-engine-action"][href*="event=click"]',
			selectorOnHover: 'a[href^="#jet-engine-action"][href*="event=hover"], [data-url^="#jet-engine-action"][data-url*="event=hover"]',

			init: function() {
				var timeout = null;

				$( document ).on( 'click.JetEngine', this.selectorOnClick, function( event ) {
					event.preventDefault();
					JetEngine.customUrlActions.actionHandler( event )
				} );

				$( document ).on( 'click.JetEngine', this.selectorOnHover, function( event ) {
					if ( 'A' === event.currentTarget.nodeName ) {
						event.preventDefault();
					}
				} );

				$( document ).on( {
					'mouseenter.JetEngine': function( event ) {

						if ( timeout ) {
							clearTimeout( timeout );
						}

						timeout = setTimeout( function() {
							JetEngine.customUrlActions.actionHandler( event )
						}, window.JetEngineSettings.hoverActionTimeout );
					},
					'mouseleave.JetEngine': function() {
						if ( timeout ) {
							clearTimeout( timeout );
							timeout = null;
						}
					},
				}, this.selectorOnHover );
			},

			actions: {},

			addAction: function( name, callback ) {
				this.actions[ name ] = callback;
			},

			actionHandler: function( event ) {
				var url = $( event.currentTarget ).attr( 'href' ) || $( event.currentTarget ).attr( 'data-url' );

				this.runAction( url );
			},

			runAction: function( url ) {
				var queryParts = url.split( '&' ),
					settings = {};

				queryParts.forEach( function( item ) {
					if ( -1 !== item.indexOf( '=' ) ) {
						var pair = item.split( '=' );

						settings[ pair[0] ] = decodeURIComponent( pair[1] );
					}
				} );

				if ( ! settings.action ) {
					return;
				}

				var actionCb = this.actions[ settings.action ];

				if ( ! actionCb ) {
					return;
				}

				actionCb( settings );
			}
		},

		prepareJetPopup: function( popupData, widgetData, $scope ) {

			var postId = null;

			if ( widgetData['is-jet-engine'] ) {
				popupData['isJetEngine'] = true;

				var $gridItems     = $scope.closest( '.jet-listing-grid__items' ),
					$gridItem      = $scope.closest( '.jet-listing-grid__item' ),
					$calendarItem  = $scope.closest( '.jet-calendar-week__day-event' ),
					$itemObject    = $scope.closest( '[data-item-object]' ),
					filterProvider = false,
					filterQueryId  = 'default';

				if ( $gridItems.length ) {
					popupData['listingSource'] = $gridItems.data( 'listing-source' );
					popupData['listingId']     = $gridItems.data( 'listing-id' );
					popupData['queryId']       = $gridItems.data( 'query-id' );
				} else {

					var $queryItems    = $scope.closest( '[data-query-id]' ),
						$listingSource = $scope.closest( '[data-listing-source]' );

					if ( $queryItems.length ) {
						popupData['queryId'] = $queryItems.data( 'query-id' );
					}

					if ( $listingSource.length ) {
						popupData['listingSource'] = $listingSource.data( 'listing-source' );
					}
				}

				if ( $gridItem.length ) {
					popupData['postId'] = $gridItem.data( 'post-id' );
					filterProvider = 'jet-engine';
				} else if ( $calendarItem.length ) {
					popupData['postId'] = $calendarItem.data( 'post-id' );
					filterProvider = 'jet-engine-calendar';
				} else if ( $itemObject ) {
					popupData['postId'] = $itemObject.data( 'item-object' );
				} else if ( window.elementorFrontendConfig && window.elementorFrontendConfig.post ) {
					popupData['postId'] = window.elementorFrontendConfig.post.id;
				}

				if ( window.JetEngineFormsEditor && window.JetEngineFormsEditor.hasEditor ) {
					popupData['hasEditor'] = true;
				}

				// Add the filtered query to the popup data
				if ( window.JetSmartFilters ) {

					switch ( filterProvider ) {
						case 'jet-engine':
							var nav = $gridItems.data( 'nav' );

							if ( nav.widget_settings?._element_id ) {
								filterQueryId = nav.widget_settings._element_id;
							}
							break;

						case 'jet-engine-calendar':
							var settings = $calendarItem.closest( '.jet-listing-calendar' ).data( 'settings' );

							if ( settings._element_id ) {
								filterQueryId = settings._element_id;
							}
							break;
					}

					filterProvider = window.JetPlugins.hooks.applyFilters( 'jet-engine.prepareJetPopupData.filterProvider', filterProvider, $scope, widgetData );
					filterQueryId  = window.JetPlugins.hooks.applyFilters( 'jet-engine.prepareJetPopupData.filterQueryId', filterQueryId, $scope, widgetData );

					if ( popupData.queryId && filterProvider
						&& window.JetSmartFilters?.filterGroups?.[ filterProvider + '/' + filterQueryId ]?.currentQuery
					) {
						popupData['filtered_query'] = window.JetSmartFilters.filterGroups[ filterProvider + '/' + filterQueryId ].currentQuery;
					}
				}

			}

			return popupData;

		},

		showCalendarEvent: function( event ) {

			var $this       = $( this ),
				$day        = $this.closest( '.jet-calendar-week__day' ),
				$week       = $day.closest( '.jet-calendar-week' ),
				$events     = $day.find( '.jet-calendar-week__day-content' ),
				activeClass = 'calendar-event-active';

			if ( $day.hasClass( activeClass ) ) {
				$day.removeClass( activeClass );
				JetEngine.activeCalendarDay.remove();
				JetEngine.activeCalendarDay = null;
				return;
			}

			if ( JetEngine.activeCalendarDay ) {
				JetEngine.activeCalendarDay.remove();
				$( '.' + activeClass ).removeClass( activeClass );
				JetEngine.activeCalendarDay = null;
			}

			$day.addClass( 'calendar-event-active' );

			JetEngine.activeCalendarDay = $( '<tr class="jet-calendar-week"><td colspan="7" class="jet-calendar-week__day jet-calendar-week__day-mobile"><div class="jet-calendar-week__day-mobile-event">' + $events.html() + '</div></td></tr>' );

			// Need for re-init popup events
			JetEngine.activeCalendarDay.find( '.jet-popup-attach-event-inited' ).removeClass( 'jet-popup-attach-event-inited' );
			JetEngine.initElementsHandlers( JetEngine.activeCalendarDay );

			JetEngine.activeCalendarDay.insertAfter( $week );

		},

		widgetListingGrid: function( $scope ) {

			var widgetID    = $scope.closest( '.elementor-widget' ).data( 'id' ),
				$wrapper    = $scope.find( '.jet-listing-grid' ).first(),
				hasLazyLoad = $wrapper.hasClass( 'jet-listing-grid--lazy-load' ),
				$listing    = $scope.find( '.jet-listing-grid__items' ).first(),
				$slider     = $listing.parent( '.jet-listing-grid__slider' ),
				$masonry    = $listing.hasClass( 'jet-listing-grid__masonry' ) ? $listing : false,
				navSettings = $listing.data( 'nav' ),
				masonryGrid = false,
				listingType = 'elementor';

			if ( ! widgetID ) {
				widgetID    = $scope.data( 'element-id' );
				listingType = $scope.data( 'listing-type' );
			}

			navSettings = JetEngine.ensureJSON( navSettings );

			if ( hasLazyLoad ) {

				var lazyLoadOptions = $wrapper.data( 'lazy-load' ),
					$container = $scope.find( '.elementor-widget-container' ),
					widgetSettings = false;

				if ( ! $container.length ) {
					$container = $scope;
				}

				// Get widget settings from `elementorFrontend` in Editor.
				if ( window.elementorFrontend && window.elementorFrontend.isEditMode()
					&& $wrapper.closest( '.elementor[data-elementor-type]' ).hasClass( 'elementor-edit-mode' )
				) {
					widgetSettings = JetEngine.getEditorElementSettings( $scope.closest( '.elementor-widget' ) );
					widgetID       = false; // for avoid get widget settings from document in editor
				}

				if ( ! widgetSettings ) {
					widgetSettings = $scope.data( 'widget-settings' );
				}

				JetEngine.lazyLoadListing( {
					container:      $container,
					elementID:      widgetID,
					postID:         lazyLoadOptions.post_id,
					queriedID:      lazyLoadOptions.queried_id || false,
					offset:         lazyLoadOptions.offset || '0px',
					query:          lazyLoadOptions.query || {},
					listingType:    listingType,
					widgetSettings: widgetSettings,
					extraProps:     lazyLoadOptions.extra_props || false,
				} );

				return;
			}

			if ( $slider.length ) {
				JetEngine.initSlider( $slider );
			}

			if ( $masonry && $masonry.length ) {

				JetEngine.initMasonry( $masonry );

				/* Keep masonry re-init for Bricks */
				if ( $scope.hasClass( 'brxe-jet-engine-listing-grid' ) ) {
					$( window ).on( 'load', function() {
						JetEngine.runMasonry( $masonry );
					} );
				}

			}

			if ( navSettings && navSettings.enabled ) {

				JetEngine.loadMoreListing( {
					container: $listing,
					settings: navSettings,
					masonry: $masonry,
					slider: $slider,
					elementID: widgetID,
				} );

			}

			// Init elements handlers in editor.
			if ( window.elementorFrontend && window.elementorFrontend.isEditMode()
				&& $wrapper.closest( '.elementor-element-edit-mode' ).length
			) {
				JetEngine.initElementsHandlers( $wrapper );
			}

		},

		initMasonry: function( $masonry, masonrySettings ) {
			imagesLoaded( $masonry, function() {
				JetEngine.runMasonry( $masonry, masonrySettings );
			} );
		},

		runMasonry: function( $masonry, masonrySettings ) {
			var defaultSettings = {
				itemSelector: '> .jet-listing-grid__item',
				columnsKey:   'columns',
			};

			masonrySettings = masonrySettings || {};
			masonrySettings = $.extend( {}, defaultSettings, masonrySettings );

			var $eWidget     = $masonry.closest( '.elementor-widget' ),
				$items       = $( masonrySettings.itemSelector, $masonry ),
				options      = $masonry.data( 'masonry-grid-options' ) || {};

			options = JetEngine.ensureJSON( options );

			// Reset masonry
			$items.css( {
				marginTop: ''
			} );

			// Bricks margin
			const { gap } = options;
			let margin = null;

			if ( gap ) {
				margin = {
					x: +gap.horizontal,
					y: +gap.vertical,
				};
			}

			var args = {
				container: $masonry[0],
				margin: margin ? margin : 0,
			};

			if ( $eWidget.length ) {
				var settings     = JetEngine.getElementorElementSettings( $eWidget ),
					breakpoints  = {},
					eBreakpoints = window.elementorFrontend.config.responsive.activeBreakpoints,
					columnsKey   = masonrySettings.columnsKey;

				args.columns = settings[columnsKey + '_widescreen'] ? +settings[columnsKey + '_widescreen'] : +settings[columnsKey];

				Object.keys( eBreakpoints ).reverse().forEach( function( breakpointName ) {

					if ( settings[columnsKey + '_' + breakpointName] ) {
						if ( 'widescreen' === breakpointName ) {
							breakpoints[eBreakpoints[breakpointName].value - 1] = +settings[columnsKey];
						} else {
							breakpoints[eBreakpoints[breakpointName].value] = +settings[columnsKey + '_' + breakpointName];
						}
					}

				} );

				args.breakAt = breakpoints;

			} else {
				args.columns = options.columns.desktop;
				args.breakAt = {
					1025: options.columns.tablet,
					768:  options.columns.mobile,
				};
			}

			var masonryInstance = Macy( args );

			masonryInstance.runOnImageLoad( function () {
				masonryInstance.recalculate( true );
			}, true );

			// Event to recalculate current masonry listings.
			$masonry.on( 'jet-engine/listing/recalculate-masonry-listing', function() {
				masonryInstance.runOnImageLoad( function () {
					masonryInstance.recalculate( true );
				}, true );
			} );

			// Event to recalculate all masonry listings.
			$( document ).on( 'jet-engine/listing/recalculate-masonry', function() {
				masonryInstance.recalculate( true );
			} );

		},

		ajaxGetListing: function( options, doneCallback, failCallback ) {

			var container = options.container || false,
				handler = options.handler || false,
				masonry = options.masonry || false,
				slider = options.slider || false,
				append = options.append || false,
				query = options.query || {},
				widgetSettings = options.widgetSettings || {},
				postID = options.postID || false,
				queriedID = options.queriedID || false,
				elementID = options.elementID || false,
				page = options.page || 1,
				preventCSS = options.preventCSS || false,
				listingType = options.listingType || false,
				extraProps = options.extraProps || false,
				isEditMode = window.elementorFrontend && window.elementorFrontend.isEditMode();

			doneCallback = doneCallback || function( response ) {};

			if ( ! container|| ! handler ) {
				return;
			}

			if ( ! preventCSS ) {
				container.css({
					pointerEvents: 'none',
					opacity: '0.5',
					cursor: 'default',
				});
			}

			var requestData = {
					action: 'jet_engine_ajax',
					handler: handler,
					query: query,
					widget_settings: widgetSettings,
					page_settings: {
						post_id: postID,
						queried_id: queriedID,
						element_id: elementID,
						page: page,
					},
					listing_type: listingType,
					isEditMode: isEditMode,
					addedPostCSS: JetEngine.addedPostCSS
				};

			if ( extraProps ) {
				Object.assign( requestData, extraProps );
			}

			$.ajax({
				url: JetEngineSettings.ajaxlisting,
				type: 'POST',
				dataType: 'json',
				data: requestData,
			}).done( function( response ) {

				// container.removeAttr( 'style' );

				// Manual reset container style to prevent removal of masonry styles.
				if ( !preventCSS ) {
					container.css( {
						pointerEvents: '',
						opacity: '',
						cursor: '',
					} );
				}

				if ( response.success ) {

					JetEngine.enqueueAssetsFromResponse( response );

					container.data( 'page', page );

					var $html = $( response.data.html );

					JetEngine.initFrontStores( $html );

					if ( slider && slider.length ) {

						var $slider = slider.find( '> .jet-listing-grid__items' );

						if ( ! $slider.hasClass( 'slick-initialized' ) ) {

							if ( append ) {
								container.append( $html );
							} else {
								container.html( $html );
							}

							var itemsCount = container.find( '> .jet-listing-grid__item' ).length;

							slider.addClass( 'jet-listing-grid__slider' );
							JetEngine.initSlider( slider, { itemsCount: itemsCount } );

						} else {
							$html.each( function( index, el ) {
								$slider.slick( 'slickAdd', el );
							});
						}

					} else {

						if ( append ) {
							container.append( $html );
						} else {
							container.html( $html );
						}

						if ( masonry && masonry.length ) {
							//JetEngine.initMasonry( masonry );
							masonry.trigger( 'jet-engine/listing/recalculate-masonry-listing' );
						}

					}

					// Re-init Bricks scripts
					JetEngine.reinitBricksScripts( elementID );

					Promise.all( JetEngine.assetsPromises ).then( function() {
						JetEngine.initElementsHandlers( $html );
						JetEngine.assetsPromises = [];
					} );

					if ( response.data.fragments ) {
						for ( var selector in response.data.fragments ) {
							var $selector = $( selector );

							if ( $selector.length ) {
								$selector.html( response.data.fragments[ selector ] );
							}
						}
					}
				}

				$( document ).trigger( 'jet-engine/listing/ajax-get-listing/done', [ $html, options ] );

			} ).done( doneCallback ).fail( function() {
				container.removeAttr( 'style' );
				if ( failCallback ) {
					failCallback.call();
				}

			} );

		},

		loadMoreListing: function( args ) {

			var instance = {

				setup: function() {
					this.container = args.container;
					this.masonry   = args.masonry;
					this.slider    = args.slider;
					this.settings  = args.settings;
					this.elementID  = args.elementID;

					this.wrapper = this.container.closest( '.jet-listing-grid' );

					this.type      = this.settings.type || 'click';
					this.page      = parseInt( this.container.data( 'page' ), 10 ) || 0;
					this.pages     = parseInt( this.container.data( 'pages' ), 10 ) || 0;
					this.queriedID = this.container.data( 'queried-id' ) || false;
				},

				init: function() {

					this.setup();

					switch ( this.type ) {
						case 'click':

							this.handleMore();

							break;

						case 'scroll':

							if ( ( ! window.elementorFrontend || ! window.elementorFrontend.isEditMode() ) && ! this.slider.length ) {
								this.handleInfiniteScroll();
							}

							break;
					}
				},

				handleMore: function() {

					if ( ! this.settings.more_el ) {
						return;
					}

					var self    = this,
						$button = $( this.settings.more_el );

					if ( ! $button.length ) {
						return;
					}

					if ( ! this.pages || this.page === this.pages && ! window.elementor ) {
						$button.css( 'display', 'none' );
					} else {
						$button.removeAttr( 'style' );
					}

					$( document )
						.off( 'click', this.settings.more_el )
						.on( 'click', this.settings.more_el, function( event ) {
							event.preventDefault();

							if ( ! self.pages || self.page >= self.pages) {
								$button.css( 'display', 'none' );
								return;
							}

							$button.css( {
								pointerEvents: 'none',
								opacity: '0.5',
								cursor: 'default',
							} );

							self.ajaxGetItems( function( response ) {
									$button.removeAttr( 'style' );

									if ( response.success && self.page === self.pages ) {
										$button.css( 'display', 'none' );
									}
								}, function() {
									$button.button.removeAttr( 'style' );
								}
							);
						} );
				},

				handleInfiniteScroll: function() {

					if ( this.container.hasClass( 'jet-listing-not-found' ) ) {
						return;
					}

					if ( ! this.pages || this.page === this.pages ) {
						return;
					}

					var self     = this,
						$trigger = this.wrapper.find( '.jet-listing-grid__loader' ),
						offset   = '0%';

					if ( ! $trigger.length ) {
						$trigger = $( '<div>', {
							class: 'jet-listing-grid__loading-trigger'
						} );

						this.wrapper.append( $trigger );
					}

					// Prepare offset value.
					if ( this.settings.widget_settings && this.settings.widget_settings.load_more_offset ) {
						var offsetValue = this.settings.widget_settings.load_more_offset;

						switch ( typeof offsetValue ) {
							case 'object':
								var size = offsetValue.size ? offsetValue.size : '0',
									unit = offsetValue.unit ? offsetValue.unit : 'px';

								offset = size + unit;
								break;

							case 'number':
							case 'string':
								offset = offsetValue + 'px';
								break;
						}
					}

					var observer = new IntersectionObserver(
						function( entries, observer ) {

							if ( entries[0].isIntersecting ) {

								self.ajaxGetItems( function() {

									// Re-init observer if the last page is not loaded
									if ( self.page !== self.pages ) {
										setTimeout( function() {
											observer.observe( entries[0].target );
										}, 250 );
									}

								} );

								// Detach observer
								observer.unobserve( entries[0].target );
							}
						},
						{
							rootMargin: '0% 0% ' + offset + ' 0%',
						}
					);

					observer.observe( $trigger[0] );
				},

				ajaxGetItems: function( doneCallback, failCallback ) {
					var self = this;

					this.page++;

					this.wrapper.addClass( 'jet-listing-grid-loading' );

					JetEngine.ajaxGetListing( {
							handler:        'listing_load_more',
							container:      this.container,
							masonry:        this.masonry,
							slider:         this.slider,
							append:         true,
							query:          this.settings.query,
							widgetSettings: this.settings.widget_settings,
							page:           this.page,
							elementID:      this.elementID,
							queriedID:      this.queriedID,
							preventCSS:     !! this.wrapper.find( '.jet-listing-grid__loader' ).length, // Prevent CSS if listing has the loader.
						}, function( response ) {

							JetEngine.lazyLoading = false;
							self.wrapper.removeClass( 'jet-listing-grid-loading' );

							if ( doneCallback ) {
								doneCallback( response );
							}

							$( document ).trigger( 'jet-engine/listing-grid/after-load-more', [args, response] );

						}, function() {

							JetEngine.lazyLoading = false;
							self.wrapper.removeClass( 'jet-listing-grid-loading' );

							if ( failCallback ) {
								failCallback();
							}

					} );
				},
			};

			instance.init();
		},

		lazyLoadListing: function( args ) {

			var $wrapper = args.container.find( '.jet-listing-grid' ),
				observer = new IntersectionObserver(
					function( entries, observer ) {

						if ( entries[0].isIntersecting ) {

							JetEngine.lazyLoading = true;

							if ( ! $wrapper.length ) {
								$wrapper = args.container;
							}

							$wrapper.addClass( 'jet-listing-grid-loading' );

							JetEngine.ajaxGetListing( {
								handler: 'get_listing',
								container: args.container,
								masonry: false,
								slider: false,
								append: false,
								elementID: args.elementID,
								postID: args.postID,
								queriedID: args.queriedID,
								query: args.query,
								widgetSettings: args.widgetSettings,
								listingType: args.listingType,
								preventCSS: true,
								extraProps: args.extraProps,
							}, function( response ) {

								$wrapper.removeClass( 'jet-listing-grid-loading' );

								var $widget = args.container.closest( '.elementor-widget' );

								if ( ! $widget.length ) {
									$widget = args.container.closest( '.jet-listing-grid--blocks' );
								}

								if ( ! $widget.length ) {
									$widget = args.container;
								}

								if ( $widget.length ) {
									$widget.find( '.jet-listing-grid' ).first().removeClass( 'jet-listing-grid--lazy-load' );
								}

								JetEngine.widgetListingGrid( $widget );
								JetEngine.loadFrontStoresItems( $widget );
								JetEngine.lazyLoading = false;

								let needReInitFilters = false;
								let isFrontend = JetEngine.isFrontend();

								if ( isFrontend && window.JetSmartFilterSettings ) {

									if ( response.data.filters_data ) {
										$.each( response.data.filters_data, function( param, data ) {
											if ( 'extra_props' === param ) {
												window.JetSmartFilterSettings[ param ] = $.extend(
													{},
													window.JetSmartFilterSettings[ param ],
													data
												);
											} else {
												if ( window.JetSmartFilterSettings[ param ]['jet-engine'] ) {
													window.JetSmartFilterSettings[ param ]['jet-engine'] = $.extend(
														{},
														window.JetSmartFilterSettings[ param ]['jet-engine'],
														data
													);
												} else {
													window.JetSmartFilterSettings[ param ]['jet-engine'] = data;
												}
											}
										});

										needReInitFilters = true;
									}

									if ( response.data.indexer_data ) {
										const {
											provider = false,
											query = {}
										} = response.data.indexer_data;

										window.JetSmartFilters.setIndexedData( provider, query );
									}
								}

								// ReInit filters
								if ( needReInitFilters && window.JetSmartFilters ) {
									window.JetSmartFilters.reinitFilters();
								}

								$( document ).trigger( 'jet-engine/listing-grid/after-lazy-load', [ args, response ] );

							}, function() {
								JetEngine.lazyLoading = false;

								if ( ! $wrapper.length ) {
									$wrapper = args.container;
								}

								$wrapper.removeClass( 'jet-listing-grid-loading' );
							} );

							// Detach observer after the first load the listing
							observer.unobserve( entries[0].target );
						}
					},
					{
						rootMargin: '0% 0% ' + args.offset + ' 0%'
					}
				);

			observer.observe( args.container[0] );
		},

		ensureJSON: function( maybeJSON ) {

			if ( ! maybeJSON ) {
				return maybeJSON;
			}

			if ( 'string' === typeof maybeJSON ) {
				console.log( maybeJSON );
				//maybeJSON = JSON.parse( maybeJSON );
			}

			return maybeJSON;

		},

		initSlider: function( $slider, customOptions ) {
			var $eWidget    = $slider.closest( '.elementor-widget' ),
				options     = $slider.data( 'slider_options' ),
				windowWidth = $( window ).width(),
				tabletBP    = 1025,
				mobileBP    = 768,
				tabletSlides, mobileSlides, defaultOptions, slickOptions;

			options = JetEngine.ensureJSON( options );

			customOptions = customOptions || {};

			options = $.extend( {}, options, customOptions );

			if ( $eWidget.length ) {

				var settings     = JetEngine.getElementorElementSettings( $eWidget ),
					responsive   = [],
					deviceMode   = elementorFrontend.getCurrentDeviceMode(),
					eBreakpoints = window.elementorFrontend.config.responsive.activeBreakpoints;

				options.slidesToShow = settings.columns_widescreen ? +settings.columns_widescreen : +settings.columns;

				Object.keys( eBreakpoints ).reverse().forEach( function( breakpointName ) {

					if ( settings['columns_' + breakpointName] ) {

						if ( 'widescreen' === breakpointName ) {

							responsive.push( {
								breakpoint: eBreakpoints[breakpointName].value,
								settings: {
									slidesToShow: +settings['columns'],
								}
							} );

						} else {
							var breakpointSettings = {
									breakpoint: eBreakpoints[breakpointName].value + 1,
									settings:   {
										slidesToShow: +settings['columns_' + breakpointName],
									}
								};

							if ( options.slidesToScroll > breakpointSettings.settings.slidesToShow ) {
								breakpointSettings.settings.slidesToScroll = breakpointSettings.settings.slidesToShow;
							}

							// if ( 'mobile' === breakpointName ) {
							// 	breakpointSettings.settings.slidesToScroll = 1;
							// }

							responsive.push( breakpointSettings );
						}
					}

				} );

				options.responsive = responsive;

			} else {

				// Ensure we have at least some options to avoid errors
				if ( ! options.slidesToShow ) {
					options.slidesToShow = {
						desktop: 3,
						tablet: 1,
						mobile: 1,
					}
				}

				if ( options.itemsCount <= options.slidesToShow.desktop && windowWidth >= tabletBP ) { // 1025 - ...
					$slider.removeClass( 'jet-listing-grid__slider' );
					return;
				} else if ( options.itemsCount <= options.slidesToShow.tablet && tabletBP > windowWidth && windowWidth >= mobileBP ) { // 768 - 1024
					$slider.removeClass( 'jet-listing-grid__slider' );
					return;
				} else if ( options.itemsCount <= options.slidesToShow.mobile && windowWidth < mobileBP ) { // 0 - 767
					$slider.removeClass( 'jet-listing-grid__slider' );
					return;
				}

				if ( options.slidesToShow.tablet ) {
					tabletSlides = options.slidesToShow.tablet;
				} else {
					tabletSlides = 1 === options.slidesToShow.desktop ? 1 : 2;
				}

				if ( options.slidesToShow.mobile ) {
					mobileSlides = options.slidesToShow.mobile;
				} else {
					mobileSlides = 1;
				}

				options.slidesToShow = options.slidesToShow.desktop;

				options.responsive = [
					{
						breakpoint: 1025,
						settings: {
							slidesToShow: tabletSlides,
							slidesToScroll: options.slidesToScroll > tabletSlides ? tabletSlides : options.slidesToScroll
						}
					},
					{
						breakpoint: 768,
						settings: {
							slidesToShow: mobileSlides,
							slidesToScroll: 1
						}
					}
				];
			}

			defaultOptions = {
				customPaging: function( slider, i ) {
					return $( '<span />' ).text( i + 1 ).attr( 'role', 'tab' );
				},
				slide: '.jet-listing-grid__item',
				dotsClass: 'jet-slick-dots',
			};

			slickOptions = $.extend( {}, defaultOptions, options );

			var $sliderItems = $slider.find( '> .jet-listing-grid__items' );

			if ( slickOptions.infinite ) {
				$sliderItems.on( 'init', function() {
					var $items        = $( this ),
						$clonedSlides = $( '> .slick-list > .slick-track > .slick-cloned.jet-listing-grid__item', $items );

					if ( !$clonedSlides.length ) {
						return;
					}

					JetEngine.initElementsHandlers( $clonedSlides );

					if ( $slider.find('.bricks-lazy-hidden').length ) {
						bricksLazyLoad();
					}
				} );
			}

			// Temporary solution issue with Lazy Load images + RTL on Chrome.
			// Remove after fix in Chrome.
			// See: https://github.com/Crocoblock/issues-tracker/issues/7552
			if ( slickOptions.rtl ) {
				$sliderItems.on( 'init', function() {
					var $items      = $( this ),
						$lazyImages = $( 'img[loading=lazy]', $items ),
						lazyImageObserver = new IntersectionObserver(
							function( entries, observer ) {
								entries.forEach( function( entry ) {
									if ( entry.isIntersecting ) {
										// If an image does not load, need to remove the `loading` attribute.
										if ( ! entry.target.complete ) {
											entry.target.removeAttribute( 'loading' );
										}

										// Detach observer
										observer.unobserve( entry.target );
									}
								} );
							}
						);

					$lazyImages.each( function() {
						const $img = $( this );
						lazyImageObserver.observe( $img[0] );
					} );
				} );
			}

			if ( $sliderItems.hasClass( 'slick-initialized' ) ) {
				$sliderItems.slick( 'refresh', true );
				return;
			}

			if ( slickOptions.variableWidth ) {
				slickOptions.slidesToShow = 1;
				slickOptions.slidesToScroll = 1;
				slickOptions.responsive = null;
			}

			$sliderItems.slick( slickOptions );
		},

		maybeReinitSlider: function( event, $scope ) {
			var $slider = $scope.find( '.jet-listing-grid__slider' );

			if ( $slider.length ) {
				$slider.each( function() {
					JetEngine.initSlider( $( this ) );
				} );
			}
		},

		widgetDynamicField: function( $scope ) {

			var $slider = $scope.find( '.jet-engine-gallery-slider' );

			if ( $slider.length ) {
				if ( $.isFunction( $.fn.imagesLoaded ) ) {
					$slider.imagesLoaded().always( function( instance ) {

						var $eWidget = $slider.closest( '.elementor-widget' );

						if ( $slider.hasClass( 'slick-initialized' ) ) {
							$slider.slick( 'refresh', true );
						} else {
							var atts = $slider.data( 'atts' );

							atts = JetEngine.ensureJSON( atts );

							if ( $eWidget.length ) {
								var settings     = JetEngine.getElementorElementSettings( $scope ),
									eBreakpoints = window.elementorFrontend.config.responsive.activeBreakpoints,
									responsive   = [];

								if ( settings.img_slider_cols || settings.img_slider_cols_widescreen ) {
									atts.slidesToShow = settings.img_slider_cols_widescreen ? +settings.img_slider_cols_widescreen : +settings.img_slider_cols;
								}

								Object.keys( eBreakpoints ).reverse().forEach( function( breakpointName ) {

									if ( settings['img_slider_cols_' + breakpointName] ) {

										if ( 'widescreen' === breakpointName ) {

											responsive.push( {
												breakpoint: eBreakpoints[breakpointName].value,
												settings:   {
													slidesToShow: +settings['img_slider_cols'],
												}
											} );

										} else {
											var breakpointSettings = {
												breakpoint: eBreakpoints[breakpointName].value + 1,
												settings:   {
													slidesToShow: +settings['img_slider_cols_' + breakpointName],
												}
											};

											responsive.push( breakpointSettings );
										}
									}

								} );

								atts.responsive = responsive;
							}

							$slider.slick( atts );
						}
					} );
				}
			}

			$slider.on('init', function (event, slick) {

				const slider = event.target;

				if (!slider.classList.contains('jet-engine-gallery-lightbox') || ! window?.PhotoSwipeLightbox) {
					return;
				}

				let lightbox = new PhotoSwipeLightbox({
					mainClass: 'brx',
					gallery: slider,
					children: 'a',
					showHideAnimationType: 'none',
					zoomAnimationDuration: false,
					pswpModule: PhotoSwipe5,
				});

				lightbox.addFilter('numItems', numItems => slick.slideCount);

				lightbox.addFilter('clickedIndex', function (clickedIndex, e) {
					const slide = e.target.closest('.slick-slide');

					if (!slide) {
						return clickedIndex;
					}

					if (clickedIndex >= slick.slideCount) {
						return clickedIndex % slick.slideCount;
					}

					return clickedIndex;
				});

				lightbox.addFilter('thumbEl', (thumbnail, itemData, index) => {
					return thumbnail;
				});

				lightbox.addFilter('thumbBounds', (thumbBounds, itemData, index) => {
					return thumbBounds;
				});

				lightbox.init();
			});

			// Masonry init
			var $masonry = $scope.find( '.jet-engine-gallery-grid--masonry' );

			if ( $masonry.length ) {
				JetEngine.initMasonry( $masonry, {
					columnsKey: 'img_columns',
					itemSelector: '> .jet-engine-gallery-grid__item',
				} );
			}

		},

		calendarCache: {

			entries: {},

			//introduced because Firefox does not have forEach method for iterators
			iterate: function( iterator, callback ) {
				if ( typeof iterator?.forEach === 'function' ) {
					iterator.forEach( callback );
				} else if ( typeof iterator?.next === 'function' ) {
					let next;
					while ( next = iterator.next(), ! next.done ) {
						callback.call( this, next.value );
					}
				}
			},

			get: function ( cacheId, month ) {
				return JetEngine.calendarCache.entries[ cacheId ]?.get( month ) || false;
			},
	
			set: function ( cacheId, month, content, settings = {}, timestamp = false ) {
				if ( ! JetEngine.calendarCache.entries[ cacheId ] ) {
					JetEngine.calendarCache.entries[ cacheId ] = new Map();
				}
	
				if ( ! JetEngine.calendarCache.entries[ cacheId ].has( month )
					&& JetEngine.calendarCache.entries[ cacheId ].size > ( settings['max_cache'] ?? 12 ) - 1
				) {
					let deletedKey;
	
					const mapKeys = JetEngine.calendarCache.entries[ cacheId ].keys();
	
					if ( settings['__switch_direction'] < 0 ) {
						let maxDate = false;
	
						JetEngine.calendarCache.iterate(
							mapKeys,
							function ( key ) {
								const parsedDate = Date.parse( key );
		
								if ( ! maxDate || parsedDate > maxDate ) {
									maxDate = parsedDate;
									deletedKey = key;
								}
							}
						);
					} else {
						let minDate = false;
	
						JetEngine.calendarCache.iterate(
							mapKeys,
							function ( key ) {
								const parsedDate = Date.parse( key );
		
								if ( ! minDate || parsedDate < minDate ) {
									minDate = parsedDate;
									deletedKey = key;
								}
							}
						);
					}
	
					JetEngine.calendarCache.entries[ cacheId ].delete( deletedKey );
				}
	
				if ( ! timestamp ) {
					timestamp = Date.now();
				}
	
				JetEngine.calendarCache.entries[ cacheId ].set( month, [ content, timestamp ] );
			},
	
			update: function ( cacheId, month, content, settings = {} ) {
				let cached = JetEngine.calendarCache.get( cacheId, month );
				JetEngine.calendarCache.set( cacheId, month, content, settings, cached[1] ?? false );
			},
	
			deleteExpiredEntries: function ( cacheId, cacheTimeout ) {
				//delete possible orphaned caches
				for ( const cacheId in JetEngine.calendarCache.entries ) {
					if ( ! document.querySelector( `.jet-calendar[data-cache-id="${cacheId}"]` ) ) {
						delete JetEngine.calendarCache.entries[ cacheId ];
					}
				}
	
				if ( ! JetEngine.calendarCache.entries[ cacheId ] ) {
					return;
				}
	
				JetEngine.calendarCache.iterate(
					JetEngine.calendarCache.entries[ cacheId ].keys(),
					function ( month ) {
						if ( JetEngine.calendarCache.isExpired( cacheId, month, cacheTimeout ) ) {
							JetEngine.calendarCache.entries[ cacheId ].delete( month );
						}
					}
				);
			},
	
			isExpired: function ( cacheId, month, cacheTimeout ) {
				if ( cacheTimeout < 0 ) {
					return false;
				}
	
				const cached = JetEngine.calendarCache.get( cacheId, month );
	
				if ( ! cached || ! Array.isArray( cached ) ) {
					return true;
				}
	
				return ! cached[1] || cached[1] < Date.now() - cacheTimeout;
			},
	
			clear: function( e, $calendar ) {
				const cacheId = $calendar.data( 'cache-id' ) || false;
	
				if ( ! cacheId ) {
					return;
				}
	
				JetEngine.calendarCache.entries[ cacheId ] = new Map();
			},

			modifyJetSmartFiltersSetiings: function( $widget, widgetType, monthData ) {
				if ( ! window.JetSmartFilterSettings || ! window.JetSmartFilterSettings.settings ) {
					return;
				}

				if ( ! window.JetSmartFilterSettings.settings['jet-engine-calendar'] ) {
					return;
				}

				monthData = monthData.split( ' ' );

				const month = monthData[0],
				      year = monthData[1];

				let widgetId;

				switch ( widgetType ) {
					case 'block':
						widgetId = $widget.closest( '.jet-listing-calendar-block' )[0].id;

						if ( ! widgetId ) {
							widgetId = 'default';
						}

						if ( window.JetSmartFilterSettings.settings['jet-engine-calendar'][ widgetId ] ) {
							window.JetSmartFilterSettings.settings['jet-engine-calendar'][ widgetId ]['start_from_month'] = month;
							window.JetSmartFilterSettings.settings['jet-engine-calendar'][ widgetId ]['start_from_year'] = year;
						}
						
						break;
					case 'bricks':
						widgetId = $widget.data( 'element-id' );

						if ( ! widgetId ) {
							break;
						}

						for ( const id in window.JetSmartFilterSettings.settings['jet-engine-calendar'] ) {
							if ( window.JetSmartFilterSettings.settings['jet-engine-calendar'][ id ]?._id === widgetId ) {
								window.JetSmartFilterSettings.settings['jet-engine-calendar'][ id ]['start_from_month'] = month;
								window.JetSmartFilterSettings.settings['jet-engine-calendar'][ id ]['start_from_year'] = year;
								break;
							}
						}

						break;
					case 'elementor':
						widgetId = $widget.closest( '.elementor-widget-jet-listing-calendar' )[0].id;

						if ( ! widgetId ) {
							widgetId = 'default';
						}

						if ( window.JetSmartFilterSettings.settings['jet-engine-calendar']?.[ widgetId ] ) {
							window.JetSmartFilterSettings.settings['jet-engine-calendar'][ widgetId ]['start_from_month'] = month;
							window.JetSmartFilterSettings.settings['jet-engine-calendar'][ widgetId ]['start_from_year'] = year;
						}

						break;
				}
			},
		},

		selectCalendarMonth: function ( $event ) {
			let wrapper = this.closest( '.jet-calendar-caption__dates' );

			if ( ! JetEngine.updateDateSelectLabels( wrapper ) ) {
				return;
			}

			JetEngine.switchCalendarMonth.bind( wrapper )()
		},

		updateDateSelectLabels: function( wrapper ) {
			let month = wrapper.querySelector( '.jet-calendar-caption__date-select.select-month' ),
			    year = wrapper.querySelector( '.jet-calendar-caption__date-select.select-year' );

			if ( ! month || ! year ) {
				return false;
			}

			let monthLabel = wrapper.querySelector( '.jet-calendar-caption__date-select-label.select-month' ),
				yearLabel = wrapper.querySelector( '.jet-calendar-caption__date-select-label.select-year' );

			wrapper.setAttribute( 'data-month', month.value + ' ' + year.value );

			const monthOption = month.querySelector( `option[value="${month.value}"]` ),
			      yearOption = year.querySelector( `option[value="${year.value}"]` );

			monthLabel.innerHTML = monthOption.innerHTML;
			yearLabel.innerHTML = yearOption.innerHTML;

			return true;
		},

		switchCalendarMonth: function( $event ) {

			var $this   = $( this ),
				$calendar = $this.closest( '.jet-calendar' ),
				$widget   = $this.closest( '.elementor-widget-jet-listing-calendar' ),
				widgetID  = $widget.closest( '.elementor-widget' ).data( 'id' ),
				settings  = $calendar.data( 'settings' ),
				post      = $calendar.data( 'post' ),
				month     = $this.data( 'month' );

			settings = JetEngine.ensureJSON( settings );

			if ( this.classList.contains( 'nav-link-prev' ) ) {
				settings['__switch_direction'] = -1;
			} else if ( this.classList.contains( 'nav-link-next' ) ) {
				settings['__switch_direction'] = 1;
			} else {
				settings['__switch_direction'] = 0;
			}

			let widgetType = 'elementor';

			// Context Gutenberg
			if ( ! $widget.length ) {
				$widget = $calendar.closest( '.jet-listing-calendar-block' );
				widgetType = 'block';
			}

			// Context Bricks
			if ( ! $widget.length ) {
				$widget = $calendar.closest( '.brxe-jet-listing-calendar' );
				widgetID = $widget.data( 'element-id' );
				widgetType = 'bricks';
			}

			JetEngine.calendarCache.modifyJetSmartFiltersSetiings( $widget, widgetType, month );

			const cacheId = $calendar.data( 'cache-id' ) || false,
			      cacheTimeout = ( settings['cache_timeout'] ?? 0 ) * 1000;

			if ( cacheId && cacheTimeout ) {
				
				JetEngine.calendarCache.deleteExpiredEntries( cacheId, cacheTimeout );

				// Remove the 'listening' and 'brx-open' classes from all matched elements to prevent
				// reinitialization issues in the accordion.
				if ( window.bricksIsFrontend ) {
					$calendar.find('.accordion-item.listening, .brxe-accordion-nested > .listening')
						.removeClass('listening brx-open');
				}

				JetEngine.calendarCache.update( cacheId, settings['prev_month'], $calendar.prop('outerHTML'), settings );

				const cached = JetEngine.calendarCache.get( cacheId, month );

				if ( cached?.length && cached[0] && ! JetEngine.calendarCache.isExpired( cacheId, month, cacheTimeout ) ) {
					let replacement = $( cached[0] );
					replacement.removeClass( 'jet-calendar-loading' );
					$calendar.replaceWith( replacement[0] );
					JetEngine.initElementsHandlers( $widget );
					JetEngine.updateDateSelectLabels( $widget[0] );
					// Re-init Bricks scripts
					JetEngine.reinitBricksScripts( widgetID );

					$( document ).trigger( 'jet-engine-request-calendar-cached', [ $widget ] );

					return;
				}
			}

			$calendar.addClass( 'jet-calendar-loading' );

			JetEngine.currentRequest = {
				jet_engine_action: 'jet_engine_calendar_get_month',
				month: month,
				settings: settings,
				post: post,
			};

			$( document ).trigger( 'jet-engine-request-calendar' );

			$.ajax({
				url: JetEngineSettings.ajaxlisting,
				type: 'POST',
				dataType: 'json',
				data: JetEngine.currentRequest,
			}).done( function( response ) {
				if ( response.success ) {
					$calendar.replaceWith( response.data.content );

					if ( cacheId && cacheTimeout ) {
						JetEngine.calendarCache.set( cacheId, month, response.data.content, settings );
					}

					JetEngine.initElementsHandlers( $widget );
					// Re-init Bricks scripts
					JetEngine.reinitBricksScripts( widgetID );

					$( document ).trigger( 'jet-engine-request-calendar-done', [ $widget ] );
				}
				$calendar.removeClass( 'jet-calendar-loading' );
			} );
		},

		initElementsHandlers: function( $selector ) {

			// Actual init
			window.JetPlugins.init( $selector );

			// Legacy Elementor-only init
			$selector.find( '[data-element_type]' ).each( function() {

				var $this       = $( this ),
					elementType = $this.data( 'element_type' );

				if ( !elementType ) {
					return;
				}

				if ( ! window?.elementorFrontend?.hooks?.doAction ) {
					return;
				}

				if ( 'widget' === elementType ) {
					elementType = $this.data( 'widget_type' );
					window.elementorFrontend.hooks.doAction( 'frontend/element_ready/widget', $this, $ );
				}

				window.elementorFrontend.hooks.doAction( 'frontend/element_ready/global', $this, $ );
				window.elementorFrontend.hooks.doAction( 'frontend/element_ready/' + elementType, $this, $ );

			} );

			if ( window.elementorFrontend ) {
				const elementorLazyLoad = new Event( "elementor/lazyload/observe" );
				document.dispatchEvent( elementorLazyLoad );
			}

			if ( window.JetPopupFrontend && window.JetPopupFrontend.initAttachedPopups ) {
				window.JetPopupFrontend.initAttachedPopups( $selector );
			}

		},

		getElementorElementSettings: function( $scope ) {

			if ( window.elementorFrontend && window.elementorFrontend.isEditMode() && $scope.hasClass( 'elementor-element-edit-mode' ) ) {
				return JetEngine.getEditorElementSettings( $scope );
			}

			return $scope.data( 'settings' ) || {};
		},

		getEditorElementSettings: function( $scope ) {
			var modelCID = $scope.data( 'model-cid' ),
				elementData;

			if ( ! modelCID ) {
				return {};
			}

			if ( ! window.elementorFrontend.hasOwnProperty( 'config' ) ) {
				return {};
			}

			if ( ! window.elementorFrontend.config.hasOwnProperty( 'elements' ) ) {
				return {};
			}

			if ( ! window.elementorFrontend.config.elements.hasOwnProperty( 'data' ) ) {
				return {};
			}

			elementData = window.elementorFrontend.config.elements.data[ modelCID ];

			if ( ! elementData ) {
				return {};
			}

			return elementData.toJSON();
		},

		debounce: function( threshold, callback ) {
			var timeout;

			return function debounced( $event ) {
				function delayed() {
					callback.call( this, $event );
					timeout = null;
				}

				if ( timeout ) {
					clearTimeout( timeout );
				}

				timeout = setTimeout( delayed, threshold );
			};
		},

		updateAddedStyles: function() {
			if ( window.JetEngineSettings && window.JetEngineSettings.addedPostCSS ) {
				$.each( window.JetEngineSettings.addedPostCSS, function( ind, cssID ) {
					JetEngine.addedStyles.push( 'elementor-post-' + cssID );
					JetEngine.addedPostCSS.push( cssID );
				} );
			}
		},

		enqueueAssetsFromResponse: function( response ) {
			if ( response.data.scripts ) {
				JetEngine.enqueueScripts( response.data.scripts );
			}

			if ( response.data.styles ) {
				JetEngine.enqueueStyles( response.data.styles );
			}
		},

		enqueueScripts: function( scripts ) {
			$.each( scripts, function( handle, scriptHtml ) {
				JetEngine.enqueueScript( handle, scriptHtml )
			} );
		},

		enqueueStyles: function( styles ) {
			$.each( styles, function( handle, styleHtml ) {
				JetEngine.enqueueStyle( handle, styleHtml )
			} );
		},

		enqueueScript: function( handle, scriptHtml ) {

			if ( -1 !== JetEngine.addedScripts.indexOf( handle ) ) {
				return;
			}

			if ( ! scriptHtml ) {
				return;
			}

			var selector = 'script[id="' + handle + '-js"]';

			if ( $( selector ).length ) {
				return;
			}

			//$( 'body' ).append( scriptHtml );

			var scriptsTags = scriptHtml.match( /<script[\s\S]*?<\/script>/gm );
			
			if ( scriptsTags.length ) {

				for ( var i = 0; i < scriptsTags.length; i++ ) {

					JetEngine.assetsPromises.push(
						new Promise( function( resolve, reject ) {

							var $tag = $( scriptsTags[i] );

							if ( $tag[0].src ) {

								var tag = document.createElement( 'script' );

								tag.type   = $tag[0].type;
								tag.src    = $tag[0].src;
								tag.id     = $tag[0].id;
								tag.async  = false;
								tag.onload = function() {
									resolve();
								};

								document.body.append( tag );
							} else {
								$( 'body' ).append( scriptsTags[i] );
								resolve();
							}
						} )
					);
				}
			}

			JetEngine.addedScripts.push( handle );
		},

		enqueueStyle: function( handle, styleHtml ) {

			if ( -1 !== handle.indexOf( 'google-fonts' ) ) {
				JetEngine.enqueueGoogleFonts( handle, styleHtml );
				return;
			}

			if ( -1 !== JetEngine.addedStyles.indexOf( handle ) ) {
				return;
			}

			var selector = 'link[id="' + handle + '-css"],style[id="' + handle + '"]';

			if ( $( selector ).length ) {
				return;
			}

			$( 'head' ).append( styleHtml );

			JetEngine.addedStyles.push( handle );

			if ( -1 !== handle.indexOf( 'elementor-post' ) ) {
				var postID = handle.replace( 'elementor-post-', '' );
				JetEngine.addedPostCSS.push( postID );
			}
		},

		enqueueGoogleFonts: function( handle, styleHtml ) {

			var selector = 'link[id="' + handle + '-css"]';

			if ( $( selector ).length ) {}

			$( 'head' ).append( styleHtml );
		},

		isFrontend: function () {
			// Check the Elementor
			if (typeof window.elementorFrontend !== 'undefined') {
				return !window.elementorFrontend.isEditMode();
			}

			// Check the Bricks
			if (typeof window.bricksIsFrontend !== 'undefined') {
				return window.bricksIsFrontend;
			}

			// If no builders are found, we assume it is frontend.
			return true;
		},

		reinitBricksScripts: function (elementId) {
			if (!window.bricksIsFrontend) {
				return;
			}

			document.dispatchEvent(
				new CustomEvent("bricks/ajax/query_result/displayed", {
					detail: {
						queryId: elementId || null
					}
				})
			);
		},

		filters: ( function() {

			var callbacks = {};

			return {

				addFilter: function( name, callback ) {

					if ( ! callbacks.hasOwnProperty( name ) ) {
						callbacks[name] = [];
					}

					callbacks[name].push(callback);

				},

				applyFilters: function( name, value, args ) {

					if ( ! callbacks.hasOwnProperty( name ) ) {
						return value;
					}

					if ( args === undefined ) {
						args = [];
					}

					var container = callbacks[ name ];
					var cbLen     = container.length;

					for (var i = 0; i < cbLen; i++) {
						if (typeof container[i] === 'function') {
							value = container[i](value, args);
						}
					}

					return value;
				}

			};

		})()

	};

	$( window ).on( 'elementor/frontend/init', JetEngine.init );

	window.JetEngine = JetEngine;

	document.addEventListener( 'jet-smart-filters/inited', function() {
		window.JetSmartFilters.events.subscribe( 'ajaxFilters/updated', function( provider, queryId ) {
			window.JetEngine.initFrontStores( window.JetSmartFilters?.filterGroups?.[ provider + '/' + queryId ]?.$provider);
		} );
	} );

	JetEngine.commonInit();

	window.addEventListener( 'DOMContentLoaded', function() {
		setTimeout( () => JetEngine.initBlocks() );
		JetEngine.initDone = true;
	} );

	window.jetEngineBricks = function() {
		JetEngine.initBricks();
	}

	$( window ).trigger( 'jet-engine/frontend/loaded' );

}( jQuery ) );
