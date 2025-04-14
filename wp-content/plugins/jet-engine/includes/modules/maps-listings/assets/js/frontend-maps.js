( function( $ ) {

	"use strict";

	var mapProvider = new window.JetEngineMapsProvider();

	var JetEngineMaps = {

		markersData:    {},
		clusterersData: {},
		mapProvider: mapProvider,

		preventPanToMarker: false,

		init: function() {

			var widgets = {
				'jet-engine-maps-listing.default' : JetEngineMaps.widgetMap,
			};

			$.each( widgets, function( widget, callback ) {
				window.elementorFrontend?.hooks?.addAction( 'frontend/element_ready/' + widget, callback );
			});

		},

		initBlocks: function( $scope ) {

			$scope = $scope || $( 'body' );

			window.JetPlugins.init( $scope, [
				{
					block: 'jet-engine/maps-listing',
					callback: JetEngineMaps.widgetMap
				}
			] );

		},

		initBricks: function( $scope ) {

			$scope = $scope || $( 'body' );

			window.JetPlugins.init( $scope, [
				{
					block: 'jet-engine/bricks-maps-listing',
					callback: JetEngineMaps.bricksWidgetMap
				}
			] );

		},

		commonInit: function() {
			// Register URL Action.
			if ( undefined === window.JetEngine ) {
				$( window ).on( 'jet-engine/frontend/loaded', JetEngineMaps.registerUrlAction );
			} else {
				JetEngineMaps.registerUrlAction();
			}
		},

		bricksWidgetMap: function( $scope ) {

			if (JetEngineMaps.isBricksHiddenWrap($scope)) {
				JetEngineMaps.initMapAfterDisplayingWidgets($scope[0]);
				return;
			}

			JetEngineMaps.widgetMap($scope);
		},

		widgetMap: function( $scope ) {

			var $container = $scope.find( '.jet-map-listing' ),
				mapID = $scope.data( 'id' ),
				map,
				init,
				markers,
				bounds,
				general,
				gmMarkers = [],
				activeInfoWindow,
				width,
				offset,
				mapSettings,
				autoCenter,
				customCenter,
				markerCluster;

			if ( ! $container.length || $container.attr( 'id' ) ) {
				return;
			}

			$container.attr( 'id', 'map_' + mapID + '_' + Math.floor( Math.random() * Math.floor( 999 ) ) );

			var initMarker = function( markerData ) {
				var marker,
					infowindow,
					popup,
					popupOpenOn = undefined !== general.popupOpenOn ? general.popupOpenOn : 'click',
					pinData = {
						position: { lat: markerData.latLang.lat, lng: markerData.latLang.lng },
						map: map,
						shadow: false,
					};

				if ( markerData.custom_marker ) {
					pinData.content = markerData.custom_marker;
				} else if ( general.marker && 'image' === general.marker.type ) {
					pinData.content = '<img src="' + general.marker.url + '" class="jet-map-marker-image" alt="" style="cursor: pointer;">';
				} else if ( general.marker && 'text' === general.marker.type ) {
					pinData.content = general.marker.html.replace( '_marker_label_', markerData.label );
				} else if ( general.marker && 'icon' === general.marker.type ) {
					pinData.content = general.marker.html;
				}

				pinData.markerClustering = markerData.markerClustering;

				marker = mapProvider.addMarker( pinData );

				gmMarkers.push( marker );

				JetEngineMaps.addMarkerData( markerData.id, marker, mapID );

				if ( bounds && marker ) {
					bounds.extend( mapProvider.getMarkerPosition( marker ) );
				}

				infowindow = mapProvider.addPopup( {
					position: { lat: markerData.latLang.lat, lng: markerData.latLang.lng },
					width: width,
					offset: offset,
					map: map,
				} );

				mapProvider.closePopup( infowindow, function() {
					if ( activeInfoWindow?.map ) {
						activeInfoWindow.map.isInternalInteraction = false;
					}

					activeInfoWindow = false;
				}, map );

				mapProvider.openPopup( marker, function( event ) {
					// Prevent clicking on a point of interest under listing marker
					if ( event && event.stopPropagation ) {
						event.stopPropagation();
					}

					if ( infowindow.contentIsSet() ) {

						if ( activeInfoWindow === infowindow ) {
							return;
						}

						if ( activeInfoWindow ) {
							activeInfoWindow.close();
						}

						infowindow.setMap( map );
						infowindow.draw();
						infowindow.open( map, marker );

						JetEngineMaps.initHandlers( $container.find( '.jet-map-box' ) );

						activeInfoWindow = infowindow;

						setCenterByMarker( marker );

						return;

					} else if ( general.popupPreloader ) {

						if ( activeInfoWindow ) {
							activeInfoWindow.close();
							activeInfoWindow = false;
						}

						infowindow.setMap( map );
						infowindow.draw();

						infowindow.setContent( '<div class="jet-map-preloader is-active"><div class="jet-map-loader"></div></div>', false );

						infowindow.open( map, marker );

					}

					var querySeparator = general.querySeparator || '?';
					var api = general.api +
					          querySeparator +
							  'listing_id=' + general.listingID +
							  '&post_id=' +
							  markerData.id +
							  '&source=' + general.source +
							  '&geo_query_distance=' + markerData.geo_query_distance;
					var queriedID = $container.data( 'queried-id' );

					if ( queriedID ) {
						api += '&queried_id=' + queriedID;
					}

					if ( mapID ) {
						api += '&element_id=' + mapID;
					}

					if ( window.JetSmartFilters?.filterGroups ) {
						const filterGroups = window.JetSmartFilters.filterGroups;

						for ( const groupName in filterGroups ) {
							if ( ! groupName.includes( 'jet-engine-maps/' ) ) {
								continue;
							}

							if ( filterGroups[ groupName ]?.$provider?.[0] === $container[0] ) {
								const filtersUrl = filterGroups[ groupName ].getUrl( true );

								if ( filtersUrl ) {
									api += '&jsf=' + filtersUrl.replace( '?jsf=', '' );
								}

								break;
							}
						}
					}

					jQuery.ajax({
						url: api,
						type: 'GET',
						dataType: 'json',
						beforeSend: function( jqXHR ) {
							var nonce = window.JetEngineSettings ? window.JetEngineSettings.restNonce : general.restNonce;
							jqXHR.setRequestHeader( 'X-WP-Nonce', nonce );
						},
					}).done( function( response ) {

						if ( activeInfoWindow ) {
							activeInfoWindow.close();
						}

						infowindow.setMap( map );
						infowindow.draw();

						infowindow.setContent( response.html, false );

						infowindow.open( map, marker );

						JetEngineMaps.initHandlers( $container.find( '.jet-map-box' ) );

						activeInfoWindow = infowindow;

						// Re-init Bricks scripts
						JetEngineMaps.reinitBricksScripts( mapID );

					}).fail( function( error ) {

						if ( activeInfoWindow ) {
							activeInfoWindow.close();
						}

						infowindow.setContent( error, true );
						infowindow.open( map, marker );

						activeInfoWindow = infowindow;

					});

					setCenterByMarker( marker );

				}, infowindow, map, popupOpenOn );

			};

			var setCenterByMarker = function( marker ) {

				if ( JetEngineMaps.preventPanToMarker ) {
					return;
				}

				if ( ! general.centeringOnOpen ) {
					return;
				}

				mapProvider.panTo( {
					map: map,
					position: mapProvider.getMarkerPosition( marker ),
					zoom: general.zoomOnOpen ? +general.zoomOnOpen : false,
				} );

				map.isInternalInteraction = false;
			};

			var setAutoCenter = function() {

				if ( ! bounds ) {
					return;
				}

				if ( bounds.isEmpty && bounds.isEmpty() ) {
					return;
				}

				mapProvider.setAutoCenter( {
					map: map,
					settings: general,
					bounds: bounds,
				} );

			};

			init       = $container.data( 'init' );
			markers    = $container.data( 'markers' );
			general    = $container.data( 'general' );
			autoCenter = general.autoCenter;

			if ( ! autoCenter ) {
				customCenter = general.customCenter;
			}

			mapSettings = {
				zoomControl: true,
				fullscreenControl: true,
				streetViewControl: true,
				mapTypeControl: true,
			};

			mapSettings = $.extend( {}, mapSettings, init );

			if ( ! autoCenter && customCenter ) {
				mapSettings.center = customCenter;
				mapSettings.zoom   = general.customZoom;
			}

			if ( general.maxZoom ) {
				mapSettings.maxZoom = general.maxZoom;
			}

			if ( general.minZoom ) {
				mapSettings.minZoom = general.minZoom;
			}

			if ( general.styles ) {
				mapSettings.styles = general.styles;
			}

			if ( general.advanced ) {
				
				if ( general.advanced.zoom_control ) {
					mapSettings.gestureHandling = general.advanced.zoom_control;
				} else {
					mapSettings.scrollwheel = false;
				}

				if ( undefined !== general.advanced.scrollwheel ) {
					mapSettings.scrollwheel = general.advanced.scrollwheel;
				}

			}

			map    = mapProvider.initMap( $container[0], mapSettings );
			
			bounds = mapProvider.initBounds();
			width  = parseInt( general.width, 10 );
			offset = parseInt( general.offset, 10 );

			$container.data( 'mapInstance', map );

			if ( markers ) {
				$.each( markers, function( index, markerData ) {
					markerData.markerClustering = general.markerClustering;
					initMarker( markerData );
				});
			}

			if ( autoCenter || ! customCenter ) {
				setAutoCenter();
			}

			if ( general.markerClustering ) {
				
				markerCluster = mapProvider.getMarkerCluster( {
					map: map,
					markers: gmMarkers,
					clustererImg: general.clustererImg,
					clusterMaxZoom: general.clusterMaxZoom,
					clusterRadius: general.clusterRadius,
				} );

				JetEngineMaps.clusterersData[ mapID ] = markerCluster;
			}

			$scope.on( 'jet-filter-custom-content-render', function( event, response ) {

				if ( activeInfoWindow ) {
					activeInfoWindow.close();
				}

				if ( markerCluster ) {
					mapProvider.removeMarkers( markerCluster, gmMarkers );
				}

				gmMarkers.forEach( function( marker ) {
					mapProvider.removeMarker( marker );
				} );

				gmMarkers.splice( 0, gmMarkers.length );
				JetEngineMaps.restoreMarkerData();

				bounds = mapProvider.initBounds();

				if ( response.markers.length ) {

					for ( var i = 0; i < response.markers.length; i++ ) {
						let marker = response.markers[ i ];
						marker.markerClustering = general.markerClustering;
						initMarker( marker );
					}

					if ( markerCluster ) {
						mapProvider.addMarkers( markerCluster, gmMarkers );
					}

				}

				if ( ! map.jeFiltersAutoCenterBlock && ( autoCenter || ! customCenter ) ) {
					setAutoCenter();
				}

			} );

		},

		addMarkerData: function( id, marker, mapID ) {

			if ( ! this.markersData[id] ) {
				this.markersData[id] = [];
			}

			this.markersData[id].push( {
				marker: marker,
				clustererIndex: mapID
			} );
		},
		restoreMarkerData: function() {
			this.markersData = {};
		},

		registerUrlAction: function() {
			window.JetEngine.customUrlActions.addAction(
				'open_map_listing_popup',
				JetEngineMaps.openMapListingPopup
			);
		},

		openMapListingPopup: function( settings ) {

			if ( ! settings.id ) {
				return;
			}

			var popupID = settings.id,
				zoom = settings.zoom ? +settings.zoom : false;

			if ( undefined === JetEngineMaps.markersData[ popupID ] ) {
				return;
			}

			if ( zoom ) {
				JetEngineMaps.preventPanToMarker = true;
			}

			for ( var i = 0; i < JetEngineMaps.markersData[ popupID ].length; i++ ) {

				var marker = JetEngineMaps.markersData[ popupID ][i].marker,
					map = mapProvider.getMarkerMap( marker );

				if ( !map ) {
					// A marker inside a cluster
					var clustererIndex   = JetEngineMaps.markersData[ popupID ][i].clustererIndex,
						markersClusterer = JetEngineMaps.clusterersData[ clustererIndex ];

					mapProvider.fitMapToMarker( marker, markersClusterer, zoom );
				} else {
					// Centering the map
					mapProvider.panTo( {
						map: map,
						position: mapProvider.getMarkerPosition( marker ),
						zoom: zoom,
					} );
				}

				mapProvider.triggerOpenPopup( marker );

				if ( settings.scroll_to_map ) {
					const container = mapProvider.getContainer( map );
					const offset = $( container ).offset();
					
					$( 'html, body' ).animate( { scrollTop: ( offset.top - 30 ) } );
				}
			}

			JetEngineMaps.preventPanToMarker = false;
		},

		customInitMapBySelector: function( $selector ) {
			var $mapBlock = $selector.closest( '[data-is-block="jet-engine/maps-listing"]' ),
				$mapBricks = $selector.closest( '[data-is-block="jet-engine/bricks-maps-listing"]' ),
				$mapElWidget = $selector.closest( '.elementor-widget-jet-engine-maps-listing' );

			if ( $mapBlock.length ) {
				JetEngineMaps.widgetMap( $mapBlock );
			}

			if ( $mapBricks.length ) {
				JetEngineMaps.bricksWidgetMap( $mapBricks );
			}

			if ( $mapElWidget.length ) {
				window.elementorFrontend.hooks.doAction( 'frontend/element_ready/widget', $mapElWidget, $ );
				window.elementorFrontend.hooks.doAction( 'frontend/element_ready/global', $mapElWidget, $ );
				window.elementorFrontend.hooks.doAction( 'frontend/element_ready/' + $mapElWidget.data( 'widget_type' ), $mapElWidget, $ );
			}
		},

		initHandlers: function( $selector ) {

			// Actual init
			window.JetPlugins.init( $selector );

			// Legacy Elementor-only init
			//
			// added check for window.elementorFrontend.hooks to prevent JS error if Elementor Listing Item is used in Block editor Map Listing
			// https://github.com/Crocoblock/issues-tracker/issues/13998
			if ( window?.elementorFrontend?.hooks ) {
				$selector.find( '[data-element_type]' ).each( function() {
					var $this       = $( this ),
					    elementType = $this.data( 'element_type' );
	
					if ( !elementType ) {
						return;
					}
	
					if ( 'widget' === elementType ) {
						elementType = $this.data( 'widget_type' );
						window.elementorFrontend.hooks.doAction( 'frontend/element_ready/widget', $this, $ );
					}
	
					window.elementorFrontend.hooks.doAction( 'frontend/element_ready/global', $this, $ );
					window.elementorFrontend.hooks.doAction( 'frontend/element_ready/' + elementType, $this, $ );
	
				} );
			}

			if ( window.JetPopupFrontend && window.JetPopupFrontend.initAttachedPopups ) {
				window.JetPopupFrontend.initAttachedPopups( $selector );
			}

			// Reinit the common events for a map popup.
			// Only for Google provider as event propagation is disabled.
			if ( window.JetEngine && mapProvider.getId && 'google' === mapProvider.getId() ) {
				JetEngine.commonEvents( $selector );
			}

		},

		// Restart the map when it is displayed
		initMapAfterDisplayingWidgets: function( node ) {
			const observer = new IntersectionObserver((entries, observer) => {
				entries.forEach(entry => {
					if (entry.isIntersecting) {
						observer.unobserve(entry.target);

						JetEngineMaps.widgetMap($(entry.target));
					}
				});
			});

			observer.observe(node);
		},

		// Check the Bricks parent node. Is it hidden or not?
		// The problem was the accordion and tabs
		isBricksHiddenWrap: function( $scope ) {
			const generalWrapper = $scope[0].closest('.brxe-accordion-nested, .brxe-tabs-nested');
			const wrapHidden = $scope[0].closest('.listening, .tab-pane');

			if (generalWrapper && wrapHidden && !wrapHidden.classList.contains('brx-open')) {
				return true;
			}

			return false;
		},

		//map - map instance
		//bounds - an object containing 'south', 'west', 'north', 'east' coordinates
		dispatchMapSyncEvent: function( map, bounds ) {

			const mapDiv = mapProvider.getContainer( map );
			//debounce event dispatch to prevent unnecessary filter requests on zoom/pan change
			clearTimeout( mapDiv?.JetEngineMapDebounceTimer );

			let debounceTime = + ( JetEngineSettings?.mapSyncFilter?.debounceTime ?? 500 );
			
			if ( ! isFinite( debounceTime ) ) {
				debounceTime = 1000;
			}

			mapDiv.JetEngineMapDebounceTimer = setTimeout(
				JetEngineMaps.dispatchMapSyncEventImmediate,
				debounceTime,
				mapDiv,
				bounds,
				map
			);
		},

		dispatchMapSyncEventImmediate: function( mapDiv, bounds, map ) {
			if ( map.isInternalInteraction ) {
				map.isInternalInteraction = false;
				return;
			}

			const event = new CustomEvent(
				"jet-engine/maps/update-sync-bounds",
				{
					detail: {
						div: mapDiv,
						bounds: bounds,
						map: map,
						mapProvider
					},
				}
			);
			
			document.dispatchEvent( event );
		},

		dispatchMapSyncInitEvent: function( map ) {
			const event = new CustomEvent(
				"jet-engine/maps/init-sync-bounds",
				{
					detail: {
						map: map,
						div: mapProvider.getContainer( map ),
						mapProvider
					},
				}
			);
			
			document.dispatchEvent( event );
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
		}
	};

	$( window ).on( 'elementor/frontend/init', JetEngineMaps.init );

	window.addEventListener( 'DOMContentLoaded', function() {
		JetEngineMaps.initBlocks();
		JetEngineMaps.initBricks();
	} );

	window.jetEngineMapsBricks = function() {
		JetEngineMaps.initBricks();
	}

	window.JetEngineMaps = JetEngineMaps;

	JetEngineMaps.commonInit();

	$( window ).trigger( 'jet-engine/frontend-maps/loaded' );

}( jQuery ) );
