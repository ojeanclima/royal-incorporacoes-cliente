(function( $ ) {

	'use strict';

	Vue.component( 'jet-merged-query', {
		template: '#jet-merged-query',
		props: [ 'value', 'dynamic-value' ],
		mixins: [
			window.JetQueryWatcherMixin,
			window.JetQueryRepeaterMixin,
		],
		data() {
			return {
				allowedQueryTypes: window.jet_query_component_merged_query.allowed_types,
				allowedQueires: window.jet_query_component_merged_query.allowed_queires,
				query: {},
			};
		},
		created() {
			this.query = { ...this.value };

			if ( ! this.query.base_query_type ) {
				this.$set( this.query, 'base_query_type', 'posts' );
			}

			if ( ! this.query.queries ) {
				this.$set( this.query, 'queries', [] );
			}
		},
		computed: {
			queryTypes() {

				const result = [];

				for ( const typeSlug in this.allowedQueryTypes ) {
					result.push( {
						value: typeSlug,
						label: this.allowedQueryTypes[ typeSlug ],
					} );
				}

				return result;

			}
		},
		methods: {
			allowedQueries( currentID ) {
				
				const queries = this.allowedQueires[ this.query.base_query_type ] || [];
				const excludeIDs = [];

				if ( this.query.queries.length ) {
					for ( var i = 0; i < this.query.queries.length; i++ ) {
						excludeIDs.push( this.query.queries[ i ].query_id );
					}
				}

				return queries.filter( ( item ) => {

					if ( currentID && currentID == item.value ) {
						return true;
					}

					return ! excludeIDs.includes( item.value );

				} );

			},
			queryName( queryID ) {

				for ( const typeSlug in this.allowedQueryTypes ) {
					for ( var i = 0; i < this.allowedQueires[ typeSlug ].length; i++ ) {
						if ( queryID == this.allowedQueires[ typeSlug ][ i ].value ) {
							return this.allowedQueires[ typeSlug ][ i ].label;
						}
					}
				}

				return queryID;

			},
			resetQueries() {
				this.$set( this.query, 'queries', [] );
			}
		}
	} );

})( jQuery );
