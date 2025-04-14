Vue.component( 'jet-table-delete-dialog', {
	name: 'jet-table-delete-dialog',
	template: '#jet-table-delete-dialog',
	props: {
		value: {
			type: Boolean,
			default: false,
		},
		itemId: {
			type: String,
			default: '',
		},
		itemName: {
			type: String,
			default: '',
		},
	},
	data: function() {
		return {
			isVisible: this.value,
		};
	},
	watch: {
		value: function( val ) {
			this.setVisibility( val );
		}
	},
	methods: {
		handleCancel: function() {
			this.setVisibility( false );
			this.$emit( 'input', false );
			this.$emit( 'on-cancel' );
		},
		handleOk: function() {

			var self = this;

			self.setVisibility( false );

			var params = new URLSearchParams();

			params.append( 'instance', JetEngineTableDeleteDialog.instance );

			wp.apiFetch( {
				method: 'delete',
				path: JetEngineTableDeleteDialog.api_path + self.itemId + '?' + params.toString(),
			} ).then( function( response ) {

				if ( response.success ) {
					window.location = JetEngineTableDeleteDialog.redirect;
				} else {
					self.$CXNotice.add( {
						message: response.message,
						type: 'error',
						duration: 15000,
					} );
				}

				self.$emit( 'input', false );
				self.$emit( 'on-ok' );

			} ).catch( function( e ) {

				self.$emit( 'input', false );
				self.$emit( 'on-ok' );

				self.$CXNotice.add( {
					message: e.message,
					type: 'error',
					duration: 7000,
				} );

			} );

		},
		setVisibility: function( value ) {

			if ( this.isVisible === value ) {
				return;
			}

			this.isVisible = value;
		},
	},
} );
