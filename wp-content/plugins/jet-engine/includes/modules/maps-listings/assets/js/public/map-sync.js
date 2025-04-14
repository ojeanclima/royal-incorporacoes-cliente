( function( $ ) {

	"use strict";

	const initMapSyncFilter = function() {

		window.JetSmartFilters.filtersList.JetEngineMapSync = 'jet-smart-filters-map-sync';
		window.JetSmartFilters.filters.JetEngineMapSync = class JetEngineMapSync extends window.JetSmartFilters.filters.BasicFilter {

			name = 'map-sync';
			mapSelector = '.jet-map-listing';
			mapDefaults = null;

			constructor( $container ) {
				
				const $filter = $container.find( '.jet-smart-filters-map-sync' );
				
				super( $container, $filter );
				
				const mapId = $container.data( 'query-id' );
				
				if ( mapId && mapId !== 'default' ) {
					//left .elementor-widget-container to ensure backward compatibility
					this.mapSelector = `#${mapId} > .jet-map-listing, #${mapId} > .elementor-widget-container > .jet-map-listing`;
				}

				document.addEventListener( 'jet-engine/maps/update-sync-bounds', this.updateBounds.bind( this ) );

				document.addEventListener( 'jet-engine/maps/init-sync-bounds', this.saveDefaults.bind( this ) );
			}

			updateBounds( e ) {
				if ( e.detail.div !== document.querySelector( this.mapSelector ) ) {
					return;
				}

				if ( ! this.mapDefaults ) {
					this.saveDefaults( e );
				}

				this.mapDefaults.map.jeFiltersAutoCenterBlock = true;
				
				this.dataValue = e.detail.bounds;
				this.wasChanged ? this.wasChanged() : this.wasСhanged();
			}

			saveDefaults( e ) {
				if ( this.mapDefaults !== null || e.detail.div !== document.querySelector( this.mapSelector ) ) {
					return;
				}

				this.mapDefaults = e.detail;

				const map = this.mapDefaults.map;

				this.mapDefaults.center = map.getCenter();
				this.mapDefaults.zoom   = map.getZoom();
			}

			reset() {
				super.reset();

				if ( this.mapDefaults ) {
					this.mapDefaults.map.jeFiltersAutoCenterBlock = false;
				}

				// if ( this.mapDefaults === null ) {
				// 	return;
				// }

				// this.mapDefaults.mapProvider.panTo(
				// 	{
				// 		map: this.mapDefaults.map,
				// 		position: this.mapDefaults.center,
				// 		zoom: this.mapDefaults.zoom
				// 	},
				// 	true
				// );
			}

			processData() {
				return;
			}

		};

	}

	const initFilterConflictHandler = function() {
		const conflictHandler = class FilterConflictHandler {

			isResolving = false;

			constructor() {
				this.init();
			}

			init( e ) {
				JetSmartFilters.events.subscribe( 'fiter/change', ( filter ) => {
					if ( this.isResolving ) {
						return;
					}

					this.isResolving = true;

					if ( ! [ 'map-sync', 'user-geolocation', 'location-distance' ].includes( filter?.name ) ) {
						return;
					}

					let conflictingTypes = [];
		
					if ( filter.name === 'map-sync' ) {
						conflictingTypes = [ 'user-geolocation', 'location-distance' ];
					} else {
						conflictingTypes = [ 'map-sync' ];
					}

					this.resetConflictingFilters( filter, conflictingTypes );
				} );
			}
			
			resetConflictingFilters( filter, conflictingTypes ) {
				for ( const conflictingFilter of this.getFilters( filter, conflictingTypes ) ) {
					conflictingFilter.reset();
					conflictingFilter.dataValue = false;
					conflictingFilter.wasChanged ? conflictingFilter.wasChanged() : conflictingFilter.wasСhanged();
				}

				this.isResolving = false;
			}

			getFilters( filter, types ) {
				if ( ! types.length ) {
					return [];
				}

				let filters = [];

				filter.filterGroup.filters.forEach(
					( f ) => {
						if ( ! types.includes( f.name ) ) {
							return;
						}

						filters.push( f );
					}
				);

				return filters;
			}

		};

		new conflictHandler();

	}

	document.addEventListener( 'DOMContentLoaded', ( e ) => {
		initMapSyncFilter();
	});

	document.addEventListener( 'jet-smart-filters/inited', initFilterConflictHandler );

}( jQuery ) );
