Vue.component( 'jet-list-navigation', {
	name: 'jet-list-navigation',
	template: '#jet-list-navigation',
	props: {
		totalItems: {
			type: Number,
			default: 0,
		},
		perPage: {
			type: Number,
			default: 20,
		},
		currentPage: {
			type: Number,
			default: 1,
		}
	},
	data: function() {
		return {
			initialPerPage: this.perPage,
		};
	},
	computed: {
		maxPages: function() {
			return Math.ceil( this.totalItems / this.perPage );
		},
		itemsPageCount: function() {
			var result = this.totalItems;

			if ( this.perPage < result ) {
				if ( this.currentPage < this.maxPages ) {
					result = this.perPage;
				} else if ( this.currentPage === this.maxPages ) {
					result = result - ( this.currentPage - 1 ) * this.perPage;
				}
			}

			return result;
		},
		startItemIndex: function() {

			if ( ! this.totalItems ) {
				return 0;
			}

			if ( 1 === this.currentPage ) {
				return 1;
			}

			return ( this.currentPage - 1 ) * this.perPage + 1;
		},
		endItemIndex: function() {

			if ( 1 === this.currentPage ) {
				return this.itemsPageCount;
			}

			return ( this.currentPage - 1 ) * this.perPage + this.itemsPageCount;
		},
	},
	methods: {
		changePage: function( page ) {
			this.$emit( 'change-page', page );
		},
		changePerPage: function( perPage ) {

			if ( ! perPage || '0' === perPage ) {
				perPage = this.initialPerPage;
			}

			this.$emit( 'change-per-page', Number( perPage ) );
		}
	},
} );
