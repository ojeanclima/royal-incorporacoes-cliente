(function( $ ) {

	'use strict';

	Vue.component( 'jet-ai-website-builder-models', {
		template: '#jet-ai-website-builder-models',
		data() {
			return {
				models: window.JetEngineWebsiteBuilderData.models,
				modelUID: false,
				modelHTML: '',
				isLoading: false,
				modelName: '',
				modelInfo: {
					topic: '',
					functionality: '',
				},
				isUpdatingModel: false,
				deleteModelData: false,
				deleteModelLabel: 'Delete Model',
			}
		},
		created() {

			let hash = window.location.hash;
			hash = hash.split( '/' );

			if ( hash[1] ) {
				this.modelUID = parseInt( hash[1], 10 );
				this.loadModelHTML();
			}

		},
		methods: {
			deleteModel() {

				if ( this.deletingModel ) {
					return;
				}

				this.deletingModel = true;
				this.deleteModelLabel = 'Deleteing...';

				$.ajax({
					url: window.ajaxurl,
					type: 'POST',
					dataType: 'json',
					data: {
						action: window.JetEngineWebsiteBuilderData.action,
						builder_action: 'delete_model',
						nonce: window.JetEngineWebsiteBuilderData.nonce,
						uid: this.deleteModelData.uid,
					},
				}).always( ( response ) => {
					this.deletingModel = false;
					this.deleteModelData = false;
					this.deleteModelLabel = 'Delete Model';
				} ).done( ( response ) => {
					window.location.reload();
				}).fail( ( response ) => {
					console.log( response );
				});
			},
			getModelURL( modelUID ) {

				let hash = '#' + window.JetEngineWebsiteBuilderData.subpages.model
				hash = hash.replace( '%id%', modelUID );

				return window.JetEngineWebsiteBuilderData.base_url + hash;
			},
			updateModelName() {

				this.isUpdatingModel = true;

				$.ajax({
					url: window.ajaxurl,
					type: 'POST',
					dataType: 'json',
					data: {
						action: window.JetEngineWebsiteBuilderData.action,
						builder_action: 'update_model',
						nonce: window.JetEngineWebsiteBuilderData.nonce,
						model: {
							uid: this.modelUID,
							name: this.modelName
						}
					},
				}).always( ( response ) => {
					this.isUpdatingModel = false;
				} ).done( ( response ) => {
				}).fail( ( response ) => {
					console.log( response );
				});
			},
			loadModelHTML( modelID ) {

				modelID = modelID || false;

				if ( modelID ) {
					this.modelUID = parseInt( modelID, 10 );
				}

				this.isLoading = true;

				$.ajax({
					url: window.ajaxurl,
					type: 'POST',
					dataType: 'json',
					data: {
						action: window.JetEngineWebsiteBuilderData.action,
						builder_action: 'get_model',
						nonce: window.JetEngineWebsiteBuilderData.nonce,
						uid: this.modelUID,
					},
				}).always( ( response ) => {
					this.isLoading = false;
				} ).done( ( response ) => {
					if ( ! response.success ) {
						this.modelHTML = response.data;
					} else {
						this.modelHTML = response.data.html;
						this.modelName = response.data.name;
						this.modelInfo = response.data.log_data;
						window.scrollTo({top:0});
					}
				}).fail( ( response ) => {
					console.log( response );
				});
			}
		}
	} );

	Vue.component( 'jet-ai-website-builder-section', {
		template: '#jet-ai-website-builder-section',
		props: [
			'data',
			'parent',
			'sectionTitle',
			'itemContentCallback',
			'visibilityCallback',
			'titleCallback',
			'itemsDelimiter',
			'canDeleteSection',
			'onDeleteItem',
			'onEditItem',
		],
		data() {
			return {
				expanded: true,
				editNow: false,
				newItemData: {},
			}
		},
		methods: {
			showItem( item ) {
				if ( this.visibilityCallback
					&& 'function' === typeof this.visibilityCallback
				) {
					return this.visibilityCallback( item );
				} else {
					return true;
				}
			},
			editItem( item, index ) {
				this.editNow = index;
				this.newItemData = { ...item };
			},
			isEditedItem( index ) {
				return this.editNow === index;
			},
			confirmEdit( item, index ) {
				let newItem = { ...item, ...this.newItemData };
				this.onEditItem( newItem, index );
				this.cancelEdit( index );
			},
			cancelEdit( index ) {
				this.editNow = false;
				this.newItemData = {};
			},
		}
	} );

	new Vue( {
		el: '#jet-ai-website-builder',
		template: '#jet-ai-website-builder-main',
		data: {
			promptTopic: '',
			promptFunctionality: '',
			jsonModel: {},
			error: '',
			createError: '',
			isLoading: false,
			isCreatingModel: false,
			limit: 0,
			usage: 0,
			confirmCreation: false,
			pageNow: 'create',
			hasLicense: window.JetEngineWebsiteBuilderData.has_license,
			hasJSFLicense: window.JetEngineWebsiteBuilderData.has_jsf_license,
			promptLimit: 500,
		},
		created() {
			let hash = window.location.hash;
			if ( hash && hash.includes( window.JetEngineWebsiteBuilderData.subpages.models ) ) {
				this.pageNow = 'models';
			}
		},
		computed: {
			promptLength() {
				return this.promptTopic.length + this.promptFunctionality.length;
			},
			filtersList() {

				const result = [];

				if ( ! this.jsonModel.postTypes ) {
					return result;
				}

				for ( const postTypeSlug in this.jsonModel.postTypes ) {

					const postType = this.jsonModel.postTypes[ postTypeSlug ];
					if ( postType.taxonomies && postType.taxonomies.length ) {
						for ( var i = 0; i < postType.taxonomies.length; i++ ) {

							if ( postType.taxonomies[ i ].filterType ) {

								// Path is required to delete filter if appropriate button clicked in UI
								let path = [ postTypeSlug, 'taxonomies', i ];

								result.push( {
									title: postType.taxonomies[ i ].title,
									filter_type: postType.taxonomies[ i ].filterType,
									query_type: 'Taxonomy',
									query_var: postType.taxonomies[ i ].name,
									path: path.join( '/' ),
								} );
							}
						}
					}

					if ( postType.metaFields && postType.metaFields.length ) {
						for ( var i = 0; i < postType.metaFields.length; i++ ) {
							if ( postType.metaFields[ i ].filterType ) {

								// Path is required to delete filter if appropriate button clicked in UI
								let path = [ postTypeSlug, 'metaFields', i ];
								let queryType = 'Meta Field';

								if ( 'relation' === postType.metaFields[ i ].type ) {
									queryType = 'Relation';
								}

								result.push( {
									title: postType.metaFields[ i ].title,
									filter_type: postType.metaFields[ i ].filterType,
									query_type: queryType,
									query_var: postType.metaFields[ i ].name,
									path: path.join( '/' ),
								} );
							}
						}
					}
				}

				return result;
			},
		},
		methods: {
			goToModels( event ) {
				window.location = event.target.href;
				window.location.reload();
			},
			pageURL( subpage, id ) {

				subpage = subpage || false;

				let hash = '';

				if ( subpage ) {

					hash = '#' + window.JetEngineWebsiteBuilderData.subpages[ subpage ]

					if ( id ) {
						hash = hash.replace( '%id%', id );
					}
				}

				return window.JetEngineWebsiteBuilderData.base_url + hash;
			},
			deleteCPT( postType ) {

				if ( confirm( 'Are you sure you want delete this entity from the model? All related filters and relations will be also deleted.' ) ) {

					let cpt = this.jsonModel.postTypes[ postType ];

					for ( const cpt in this.jsonModel.postTypes ) {
						if ( this.jsonModel.postTypes[ cpt ].metaFields.length ) {
							let metaFields = this.jsonModel.postTypes[ cpt ].metaFields;
							for ( var i = 0; i < metaFields.length; i++ ) {
								if ( metaFields[ i ].relatedPostType && postType === metaFields[ i ].relatedPostType ) {
									this.forceDeleteMetaField( cpt, metaFields[ i ].name );
								}
							}
						}
					}

					for ( var i = 0; i < this.jsonModel.relations.length; i++ ) {
						if (
							postType === this.jsonModel.relations[ i ].from
							|| postType === this.jsonModel.relations[ i ].to
						) {
							this.forceDeleteRelation( this.jsonModel.relations[ i ] );
						}
					}

					this.$delete( this.jsonModel.postTypes, postType );
				}
			},
			deleteFilter( filter ) {
				if ( confirm( 'Are you sure you want delete this filter from the model?' ) ) {
					this.forceDeleteFilter( filter );
				}
			},
			forceDeleteFilter( filter ) {

				if ( ! filter.path ) {
					return;
				}

				let path     = filter.path.split( '/' );
				let postType = this.jsonModel.postTypes[ path[0] ];

				if ( postType && postType[ path[1] ] && postType[ path[1] ].length ) {

					let items = [ ...postType[ path[1] ] ];
					let searchIndex = parseInt( path[2], 0 );

					delete items[ searchIndex ].filterType;

					this.$set( this.jsonModel.postTypes[ path[0] ], path[1], [ ...items ] );
				}
			},
			deleteRelation( relation ) {

				if ( confirm( 'Are you sure you want delete this realtion from the model? All related filters will be also deleted.' ) ) {

					this.forceDeleteRelation( relation );

					let parentCPT = relation.from;
					let childCPT  = relation.to;

					if ( this.jsonModel.postTypes[ parentCPT ] && this.jsonModel.postTypes[ parentCPT ].metaFields ) {
						this.forceDeleteMetaField( childCPT, relation.from, 'relatedPostType' );
					}

					if ( this.jsonModel.postTypes[ childCPT ] && this.jsonModel.postTypes[ childCPT ].metaFields ) {
						this.forceDeleteMetaField( parentCPT, relation.to, 'relatedPostType' );
					}
				}
			},
			forceDeleteRelation( relation ) {

				let filteredRelations = this.jsonModel.relations.filter( ( rel ) => {
					if ( JSON.stringify( rel ) === JSON.stringify( relation ) ) {
						return false;
					} else {
						return true;
					}
				} );

				this.jsonModel.relations = [ ...filteredRelations ];
			},
			editEntity( postType, entityType ,item, index ) {
				if ( this.jsonModel.postTypes[ postType ] ) {
					let items = [ ...this.jsonModel.postTypes[ postType ][ entityType ] ];
					items[ index ] = item;
					this.$set( this.jsonModel.postTypes[ postType ], entityType, [ ...items ] );
				}
			},
			deleteTaxonomy( postType, tax ) {

				if ( confirm( 'Are you sure you want delete this taxonomy from the model? All related filters will be also deleted.' ) ) {
					this.forceDeleteTaxonomy( postType, tax.name );
				}

			},
			forceDeleteTaxonomy( postType, taxSlug ) {

				let cpt = { ...this.jsonModel.postTypes[ postType ] }

				if ( cpt.taxonomies.length ) {

					let filteredTaxonomies = cpt.taxonomies.filter( ( tax ) => {
						if ( tax.name === taxSlug ) {
							return false;
						} else {
							return true;
						}
					} );

					this.$set( this.jsonModel.postTypes[ postType ], 'taxonomies', [ ...filteredTaxonomies ] );
				}

			},
			deleteMetaField( postType, field ) {

				if ( confirm( 'Are you sure you want delete this meta field from the model? All related filters and relations will be also deleted.' ) ) {
					this.forceDeleteMetaField( postType, field.name );
				}

			},
			forceDeleteMetaField( postType, fieldValue, deleteBy ) {

				deleteBy = deleteBy || 'name';

				let cpt = { ...this.jsonModel.postTypes[ postType ] }

				if ( cpt.metaFields.length ) {

					let filteredFields = cpt.metaFields.filter( ( field ) => {
						if ( field[ deleteBy ] && fieldValue === field[ deleteBy ] ) {
							return false;
						} else {
							return true;
						}
					} );

					this.$set( this.jsonModel.postTypes[ postType ], 'metaFields', [ ...filteredFields ] );
				}

			},
			formatSupports( supports ) {

				supports = supports || [];
				const result = [];

				for ( var i = 0; i < supports.length; i++ ) {
					result.push( { title: supports[ i ] } );
				}

				return result;
			},
			hasRelations( cpt ) {

				if ( ! cpt.metaFields.length ) {
					return false;
				}

				let relations = this.cptRelations( cpt );

				return ( 0 < relations.length ) ? true : false;

			},
			cptRelations( cpt ) {
				if ( ! cpt.metaFields.length ) {
					return false;
				}

				return cpt.metaFields.filter( ( field ) => {
					return field.type && 'relation' === field.type;
				} );
			},
			relationLabel( relationEntitySlug ) {

				const userRelTriggers = [ 'user', 'users' ];

				if ( this.jsonModel.postTypes[ relationEntitySlug ] ) {
					return this.jsonModel.postTypes[ relationEntitySlug ].title;
				} else if ( userRelTriggers.includes( relationEntitySlug ) ) {
					return 'User';
				} else {

					// search in taxonomies - if not return slug.
					for ( const cptSlug in this.jsonModel.postTypes ) {
						const cpt = this.jsonModel.postTypes[ cptSlug ];
						if ( cpt.taxonomies.length ) {
							for ( var i = 0; i < cpt.taxonomies.length; i++ ) {
								if ( cpt.taxonomies[ i ].name === relationEntitySlug ) {
									return cpt.taxonomies[ i ].title;
								}
							}
						}
					}

					return relationEntitySlug
				}
			},
			prepareModel() {

				this.isLoading = true;
				this.error = '';

				$.ajax({
					url: window.ajaxurl,
					type: 'POST',
					dataType: 'json',
					data: {
						action: window.JetEngineWebsiteBuilderData.action,
						builder_action: 'prepare_model',
						nonce: window.JetEngineWebsiteBuilderData.nonce,
						topic: this.promptTopic,
						functionality: this.promptFunctionality,
					},
				}).always( ( response ) => {
					this.isLoading = false;
				} ).done( ( response ) => {

					if ( ! response.success ) {
						// Maybe fix visual error length
						this.error = response.data.replace( '600', this.promptLimit );
					} else {
						this.jsonModel = { ...response.data.completion };
						this.usage = response.data.usage;
						this.limit = response.data.limit;
					}

				}).fail( ( response ) => {
					console.log( response );
				});

			},
			createModel() {

				this.isCreatingModel = true;
				this.error = '';

				$.ajax({
					url: window.ajaxurl,
					type: 'POST',
					dataType: 'json',
					data: {
						action: window.JetEngineWebsiteBuilderData.action,
						builder_action: 'create_model',
						nonce: window.JetEngineWebsiteBuilderData.nonce,
						model: this.jsonModel,
						topic: this.promptTopic,
						functionality: this.promptFunctionality,
					},
				}).done( ( response ) => {

					if ( ! response.success ) {
						this.createError = response.data;
					} else {
						window.location = this.pageURL( 'model', response.data.uid );
						window.location.reload();
					}

				}).always( ( response ) => {
					this.isCreatingModel = false;
				} ).fail( ( response ) => {
					this.createError = response.statusText;
					this.createError += '. In case if you checked "Use WooCommerce" checkbox - please install & activate WooCommerce plugin manually and try again. The same for filters - if you have filters but not installed JetSmartFilters plugin, install it and try again.';
				});
			},
		}
	} );

})( jQuery );
