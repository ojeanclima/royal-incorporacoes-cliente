
import RelatedItemModal from 'related-item-modal';
import RelatedItemsTable from 'related-items-table';

const {
	Button,
	ButtonGroup
} = wp.components;

const {
	render,
	Component,
	Fragment
} = wp.element;

const $ = jQuery;

class App extends Component {

	constructor( props ) {

		super( props );

		this.state = {
			connectNew: false,
			createNew: false,
			relatedID: null,
			relatedItems: [],
			allowedOptions: [],
			isBusy: false,
		};

		this.fetchItems();

		if ( this.isBlockEditor() ) {
			this.onBlockEditorSavePost();
		} else {
			$( '#post, #edittag, #your-profile, .cx-form' ).on( 'submit', this.onSubmitForm.bind( this ) );
		}

	}

	isBlockEditor() {
		return $( 'body' ).hasClass( 'block-editor-page' );
	}

	onBlockEditorSavePost() {
		var self         = this,
			editor       = wp.data.dispatch( 'core/editor' ),
			editorSelect = wp.data.select( 'core/editor' ),
			savePost     = editor.savePost;

		if ( window?.cxInterfaceBuilderAPI ) {
			savePost = window.cxInterfaceBuilderAPI.savePost;
		}

		editor.savePost = function( options ) {
			options = options || {};

			if ( options.isAutosave || options.isPreview ) {
				return savePost( options );
			}

			var isDraft = ['draft', 'auto-draft'].includes( editorSelect.getEditedPostAttribute( 'status' ) );

			if ( isDraft ) {
				return savePost( options );
			}

			if ( ! self.hasErrors() ) {
				return savePost( options );
			}

			self.scrollToFirstErrorField();
		};
	}

	hasErrors() {
		return !! document.querySelector( '.jet-engine-rels.required[data-items="0"]' );
	}

	onSubmitForm( event ) {
		if ( this.hasErrors() ) {
			this.scrollToFirstErrorField();
			event.preventDefault();
		}
	}

	highlightError( element ) {
		if ( element.classList.contains( 'blink' ) ) {
			return;
		}

		element.classList.add( 'blink' );

		setTimeout( () => {
			element.classList.remove( 'blink' );
		}, 500 );
	}

	scrollToFirstErrorField() {
		var $field = $( '.jet-engine-rels.required[data-items="0"]' ).first();

		if ( ! $field.length ) {
			return;
		}

		let element = $field[0],
		    box = element.closest( '[id^="related_jet_engine_rel_"]' );

		if ( ! box ) {
			box = element.closest( '[id^="jet_engine_rel_"]' );
		}

		element = box || element;

		let $scrollSelector = $( 'html, body' ),
		    scrollTop = $( element ).offset().top,
		    offset = 70;

		if ( this.isBlockEditor() ) {

			if ( $( 'body' ).hasClass( 'is-fullscreen-mode' ) ) {
				offset += 20;
			} else {
				offset += 60;
			}

			if ( $field.closest( '.interface-interface-skeleton__sidebar' ).length ) {
				$scrollSelector = $( '#editor .interface-interface-skeleton__sidebar' );
				offset += 50;
			} else {
				$scrollSelector = $( '#editor .interface-interface-skeleton__content' );
			}

			scrollTop += $scrollSelector.scrollTop();
		}

		$scrollSelector.stop().animate(
			{ scrollTop: scrollTop - offset },
			{
				duration: 500,
				complete: () => {
					this.highlightError( element );
				}
			}
		);
	}

	fetchItems() {

		window.wp.ajax.send(
			'jet_engine_relations_get_related_items',
			{
				type: 'GET',
				data: {
					_nonce: window.JetEngineRelationsCommon._nonce,
					relID: this.props.relID,
					objectType: this.props.controlObjectType,
					object: this.props.controlObjectName,
					currentObjectID: this.props.currentObjectID,
					isParentProcessed: this.props.isParentProcessed,
				},
				success: ( response ) => {
					this.setState( { relatedItems: [ ...response ] } );
				},
				error: ( response, errorCode, errorText ) => {

					if ( response ) {
						alert( response );
					} else {
						alert( errorText );
					}

				}
			}
		);
	}

	reorderItems( newItems, reorderedCB ) {

		this.setState( { isBusy: true } );

		window.wp.ajax.send(
			'jet_engine_relations_reorder_relation_items',
			{
				type: 'POST',
				data: {
					_nonce: window.JetEngineRelationsCommon._nonce,
					relID: this.props.relID,
					itemsOrder: newItems,
					relatedObjectID: -1,
					relatedObjectType: this.props.controlObjectType,
					relatedObjectName: this.props.controlObjectName,
					isParentProcessed: this.props.isParentProcessed,
					currentObjectID: this.props.currentObjectID,
				},
				success: ( response ) => {
					this.setState( { isBusy: false } );
					this.setState( { relatedItems: [ ...response ] } );
					if ( reorderedCB ) {
						reorderedCB();
					}
				},
				error: ( response, errorCode, errorText ) => {

					this.setState( { isBusy: false } );

					if ( response ) {
						alert( response );
					} else {
						alert( errorText );
					}

				}
			}
		);
	}

	buttonLabel( type ) {

		type = type || 'connect';

		switch( type ) {
			case 'connect':
				return this.props.labels.connectButton;
			case 'create':
				return this.props.labels.createButton;
		}
	}

	modalTitle() {

		if ( this.state.connectNew ) {
			return this.buttonLabel( 'connect' );
		}

		if ( this.state.createNew ) {
			return this.buttonLabel( 'create' );
		}

		return null;
	}

	closeModal( relatedItems ) {

		this.setState( {
			createNew: false,
			connectNew: false,
		} )

		if ( relatedItems && relatedItems.length ) {
			this.setState( { relatedItems: [ ...relatedItems ] } );
		}
	}

	canCreate() {
		return this.props.createFields && 0 < this.props.createFields.length;
	}

	getBusyStyles() {
		const style = {};

		if ( this.state.isBusy ) {
			style.pointerEvents = 'none';
			style.opacity = '0.6';
		}

		return style;
	}

	isValid() {
		return ! this.props.isRequired || this?.state?.relatedItems?.length;
	}

	render() {

		const buttonStyle = { margin: '0 10px 0 0' };

		let className = 'jet-engine-rels';
		
		if ( this.props.isRequired ) {
			className += ' required';
		}
		
		return ( <div
				className={ className }
				style={ this.getBusyStyles() }
				data-items={ this?.state?.relatedItems?.length || 0 }
			>
			{ ( this.state.connectNew || this.state.createNew ) && <RelatedItemModal
				{ ...this.props }
				title={ this.modalTitle() }
				relatedItems={ this.state.relatedItems }
				type={ ( this.state.connectNew ? 'connect' : 'create' ) }
				onClose={ ( relatedItems ) => {
					relatedItems = relatedItems || false;
					this.closeModal( relatedItems );
				} }
				onComplete={ ( relatedItems ) => {
					if ( relatedItems && relatedItems.length ) {
						this.setState( { relatedItems: [ ...relatedItems ] } );
					} else {
						this.setState( { relatedItems: [] } );
					}
					this.closeModal();
				} }
			/> }
			<ButtonGroup
				style={ {
					display: 'flex',
					gap: '10px'
				} }
			>
				{ this.canCreate() && <Button
					isSecondary
					onClick={ () => {
						this.setState( {
							createNew: true,
							connectNew: false,
						} )
					} }
				>{ this.buttonLabel( 'create' ) }</Button> }
				<Button
					isSecondary
					onClick={ () => {
						this.setState( {
							createNew: false,
							connectNew: true,
						} )
					} }
				>{ this.buttonLabel( 'connect' ) }</Button>
			</ButtonGroup>
			<RelatedItemsTable
				items={ this.state.relatedItems }
				columns={ this.props.tableColumns }
				metaFields={ this.props.metaFields }
				relID={ this.props.relID }
				currentObjectID={ this.props.currentObjectID }
				controlObjectType={ this.props.controlObjectType }
				controlObjectName={ this.props.controlObjectName }
				isParentProcessed={ this.props.isParentProcessed }
				onUpdate={ ( relatedItems ) => {
					this.setState( { relatedItems: [ ...relatedItems ] } );
				} }
				onReorder={ ( newItems, reorderedCB ) => {
					this.reorderItems( newItems, reorderedCB );
				} }
			/>
			{ ! this.isValid() && 
				<div class="jet-rel--required-error">
					{ this.props.labels?.requiredError || "Should have at least one related item." }
				</div>
			}
		</div> );
	}

}

for ( var i = 0; i < window.JetEngineRelationsControls.length; i++ ) {

	let control = window.JetEngineRelationsControls[ i ];
	const controlEl = document.getElementById( control.relEl );

	if ( controlEl ) {
		render(
			<App
				relID={ control.relID }
				metaFields={ control.metaFields }
				labels={ control.labels }
				tableColumns={ control.tableColumns }
				currentObjectID={ window.JetEngineCurrentObjectID }
				controlObjectType={ control.objectType }
				controlObjectName={ control.object }
				isParentProcessed={ control.isParentProcessed }
				createFields={ control.createFields }
				isRequired={ control.isRequired }
			/>,
			controlEl
		);
	}

}
