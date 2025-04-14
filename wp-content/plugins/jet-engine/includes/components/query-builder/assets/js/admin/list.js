(function( $, JetEngineQueryListConfig ) {

	'use strict';

	window.JetEngineQueryList = new Vue( {
		el: '#jet_query_list',
		template: '#jet-query-list',
		data: {
			itemsList: [],
			errorNotices: [],
			editLink: JetEngineQueryListConfig.edit_link,
			showDeleteDialog: false,
			deletedItem: {},
			queryTypes: JetEngineQueryListConfig.query_types,
			perPage: 20,
			currentPage: 1,
			searchKeyword: '',
			filterByType: '',
			sortBy: '',
			isIDCopied: false,
		},
		mounted: function() {

			var self = this;

			wp.apiFetch( {
				method: 'get',
				path: JetEngineQueryListConfig.api_path,
			} ).then( function( response ) {

				if ( response.success && response.data ) {
					for ( var itemID in response.data ) {
						var item = response.data[ itemID ];
						self.itemsList.push( item );
					}
				} else {
					if ( response.notices.length ) {
						response.notices.forEach( function( notice ) {
							self.errorNotices.push( notice.message );
						} );
					}
				}
			} ).catch( function( e ) {
				self.errorNotices.push( e.message );
			} );

			this.getStoredPerPageValue();
		},
		computed: {
			totalItems: function() {
				return this.filteredItemsList.length;
			},
			filteredItemsList: function() {
				var result = [ ...this.itemsList ];

				if ( this.searchKeyword ) {
					var searchKeyword = this.searchKeyword.toLowerCase();

					result = result.filter( ( item ) => {
						return ( item?.labels?.name && -1 !== item.labels.name.toLowerCase().indexOf( searchKeyword ) )
							|| ( item?.args?.description && -1 !== item.args.description.toLowerCase().indexOf( searchKeyword ) );
					} );
				}

				if ( this.filterByType ) {
					result = result.filter( ( item ) => {
						return item?.args?.query_type === this.filterByType;
					} );
				}

				if ( this.sortBy ) {

					switch ( this.sortBy ) {
						case 'title_asc':
						case 'title_desc':
							result.sort( ( a, b ) => a.labels.name.localeCompare( b.labels.name ) );

							if ( 'title_desc' === this.sortBy ) {
								result.reverse();
							}

							break;

						case 'date_asc':
							result.sort( ( a, b ) => ( a.id - b.id ) );
							break;

						case 'date_desc':
							// default sort type
							break;
					}
				}

				return result;
			},
			currentPageItems: function() {
				var offset = ( this.currentPage - 1 ) * this.perPage;
				return this.filteredItemsList.slice( offset, offset + this.perPage );
			}
		},
		methods: {
			copyID: function( itemID ) {
				if ( window.navigator.clipboard ) {

					let copiedTimeout = false;

					window.navigator.clipboard.writeText( itemID ).then( () => {

						this.isIDCopied = itemID;

						if ( copiedTimeout ) {
							clearTimeout( copiedTimeout );
						}

						copiedTimeout = setTimeout( () => {
							this.isIDCopied = false;
						}, 1500 );

					} );

				}
			},
			copyItem: function( item ) {

				if ( !item ) {
					return;
				}

				var self = this,
					newItemData = JSON.parse( JSON.stringify( item ) );

				newItemData.labels.name = newItemData.labels.name  + ' (Copy)';

				wp.apiFetch( {
					method: 'post',
					path: JetEngineQueryListConfig.api_path_add,
					data: {
						general_settings: Object.assign(
							{},
							newItemData.labels,
							newItemData.args,
							{
								slug: newItemData.slug,
							},
						),
					},
				} ).then( function( response ) {

					if ( response.success && response.item_id ) {

						newItemData.id = response.item_id;

						self.itemsList.unshift( newItemData );

						self.$CXNotice.add( {
							message: JetEngineQueryListConfig.notices.copied,
							type: 'success',
						} );

					} else {
						if ( response.notices.length ) {
							response.notices.forEach( function( notice ) {

								self.$CXNotice.add( {
									message: notice.message,
									type: 'error',
									duration: 7000,
								} );


							} );
						}
					}
				} ).catch( function( response ) {

					self.$CXNotice.add( {
						message: response.message,
						type: 'error',
						duration: 7000,
					} );

				} );
			},
			deleteItem: function( item ) {
				this.deletedItem      = item;
				this.showDeleteDialog = true;
			},
			getQueryType: function( type ) {
				for (var i = 0; i < this.queryTypes.length; i++) {
					if ( type === this.queryTypes[ i ].value ) {
						return this.queryTypes[ i ].label;
					}
				}

				return type;

			},
			getEditLink: function( id ) {
				return this.editLink.replace( /%id%/, id );
			},
			updateCurrentPage: function( page ) {
				this.currentPage = page;
			},
			updatePerPage: function( perPage ) {
				this.perPage = perPage;
				this.currentPage = 1;

				window.localStorage.setItem( 'jet_query_list_per_page', perPage );
			},
			getStoredPerPageValue: function() {
				var storedPerPage = window.localStorage.getItem( 'jet_query_list_per_page' );

				if ( storedPerPage ) {
					this.perPage = Number( storedPerPage );
				}
			},
			resetFilters: function() {
				this.searchKeyword = '';
				this.filterByType = '';
				this.sortBy = '';
			}
		}
	} );

})( jQuery, window.JetEngineQueryListConfig );
