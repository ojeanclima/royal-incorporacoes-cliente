(function( $, JetEngineTablesListConfig ) {

	'use strict';

	window.JetEngineTablesList = new Vue( {
		el: '#jet_tables_list',
		template: '#jet-tables-list',
		data: {
			itemsList: [],
			errorNotices: [],
			editLink: JetEngineTablesListConfig.edit_link,
			queries: JetEngineTablesListConfig.queries,
			showDeleteDialog: false,
			deletedItem: {},
		},
		mounted() {

			var params = new URLSearchParams();

			params.append( 'instance', JetEngineTablesListConfig.instance );

			wp.apiFetch( {
				method: 'get',
				path: JetEngineTablesListConfig.api_path + '?' + params.toString(),
			} ).then( ( response ) => {

				//console.log( response );

				if ( response.success && response.data ) {
					for ( var itemID in response.data ) {
						var item = response.data[ itemID ];
						this.itemsList.push( item );
					}
				} else {
					this.$CXNotice.add( {
						message: response.message,
						type: 'error',
						duration: 15000,
					} );
				}
			} ).catch( ( e ) => {
				this.$CXNotice.add( {
					message: e.message,
					type: 'error',
					duration: 15000,
				} );
			} );
		},
		methods: {
			deleteItem( item ) {
				this.deletedItem      = item;
				this.showDeleteDialog = true;
			},
			getEditLink( id ) {
				return this.editLink.replace( /%id%/, id );
			},
			queryLabel( id ) {
				var qLabel = this.queries[ id ] || id;
				return qLabel;
			},
			copyItem: function( item ) {

				if ( !item ) {
					return;
				}

				var self = this;

				item = JSON.parse( JSON.stringify( item ) );

				item.args.name   = item.labels.name + ' (Copy)';
				item.labels.name = item.labels.name + ' (Copy)';

				var params = new URLSearchParams();

				params.append( 'instance', JetEngineTablesListConfig.instance );

				wp.apiFetch( {
					method: 'post',
					path: JetEngineTablesListConfig.api_path_add + '?' + params.toString(),
					data: {
						general_settings: item.args,
						meta_fields: item.meta_fields
					},
				} ).then( function( response ) {

					if ( response.success && response.item_id ) {

						item.id = response.item_id;

						self.itemsList.unshift( item );

						self.$CXNotice.add( {
							message: JetEngineTablesListConfig.notices.copied,
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

				} );
			},
		}
	} );

})( jQuery, window.JetEngineTablesListConfig );
