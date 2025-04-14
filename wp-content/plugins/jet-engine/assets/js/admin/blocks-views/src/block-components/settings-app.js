import JetEngineRepeater from "../components/repeater-control.js";

// Destructure the Button and ToggleControl components from the wp.components package.
const {
	Button,
	Modal,
	TextControl,
	TextareaControl,
	SelectControl,
	Flex,
	FlexBlock,
	FlexItem
} = wp.components;

// Destructure the render function, Component base class, and Fragment component from the wp.element package.
const {
	render,
	Component,
	Fragment
} = wp.element;

const {
	MediaUpload
} = wp.blockEditor;

const { __ } = wp.i18n;

/**
 * Define the SettingsApp class, extending the React Component class to create a custom React component.
 */
class SettingsApp extends Component {

	/**
	 * Constructor method to initialize the component's state.
	 * @param {Object} props Properties passed to the component.
	 */
	constructor( props ) {

		super( props );

		// Initialize the component's state. 'is_saving' flag indicates whether settings are currently being saved.
		// 'settings' object is populated with settings passed through props.
		this.state = {
			isSaving: false,
			isSettingsOpen: false,
			componentTitle: '',
			updateProp: false,
			settings: { ...this.props.settings }
		};

		const cssVarsList = document.getElementById( 'jet_component_css_vars' );
		cssVarsList.innerHTML = this.getCSSVarsList();

	}

	/**
	 * Updates the component's state with new settings.
	 * @param {Object} updatedSettings The updated settings object.
	 */
	updateSettingsState( updatedSettings ) {
		this.setState( ( prevState ) => {

			return {
				...prevState,
				...{
					settings: {
						...prevState.settings,
						...updatedSettings,
					}
				}
			}
		} );
	}

	updateItem( item, prop, value ) {

		const newSetings = this.state.settings[ this.state.updateProp ].map( ( control, i ) => {

			if ( item._id === control._id ) {
				return { ...control, ...{ [ prop ]: value } }
			}

			return control;
		} );

		this.updateSettingsState( {
			[ this.state.updateProp ]: newSetings,
		} );

	}

	/**
	 * Handles the saving of settings by making an AJAX request to the backend.
	 */
	saveSettings() {

		this.setState( { isSaving: true } );

		window.wp.ajax.send(
			this.props.hook,
			{
				type: 'POST',
				data: {
					nonce: this.props.nonce,
					post_id: this.props.postID,
					settings: this.state.settings,
				},
				success: ( response ) => {
					this.setState( { isSaving: false } );

					const cssVarsList = document.getElementById( 'jet_component_css_vars' );

					cssVarsList.innerHTML = this.getCSSVarsList();
					
				},
				error: ( response, errorCode, errorText ) => {
					alert( response );
					this.setState( { isSaving: false } );
				}
			}
		);
	}

	getCSSVarsList() {

		const styleOptions = this.state.settings.styles;
		let result = '';

		if ( styleOptions.length ) {
			result += '<ul>';
			for ( var i = 0; i < styleOptions.length; i++ ) {
				if ( styleOptions[ i ].control_name ) {
					result += '<li style="font-size: 0.8em;font-family:monospace;">var( --jet-component-' + styleOptions[ i ].control_name + ' )</li>';
				}
			}
			result += '</ul>';
		}

		return result;

	}

	getDefaultItem() {

		const uid = Math.floor( Math.random() * 89999 ) + 10000;
		
		const defaultItem = {
			'_id': uid,
			'control_label': '',
			'control_name': '',
			'control_default': '',
		};

		if ( 'controls' === this.state.updateProp ) {
			defaultItem.control_options       = '';
			defaultItem.control_type          = 'text';
			defaultItem.control_default_image = {
				id: null,
				url: null,
				thumb: null,
			};
		}

		return defaultItem;
	}

	isControlVisible( prop, item ) {

		switch ( prop ) {
			case 'control_options':
				return ( 'select' === item.control_type ) ? true : false;

			case 'control_default':
				return ( 'media' !== item.control_type ) ? true : false;

			case 'control_default_image':
				return ( 'media' === item.control_type ) ? true : false;

		}

		return true;
	}

	saveButton() {
		return <Button
			isPrimary
			isBusy={ this.state.isSaving }
			disabled={ this.state.isSaving }
			style={ {
				width: 'auto',
				height: '36px',
				marginRight: '5px',
			} }
			onClick={ () => {
				this.saveSettings()
			} }
		>{ __( "Save Controls" ) }</Button>
	}

	/**
	 * Renders the settings application interface.
	 */
	render() {

		return ( <div className="jet-wc-product-table-settings">
			<Button
				variant="secondary"
				type="button"
				size="compact"
				style={ { width: '100%', boxSizing: 'border-box', justifyContent: 'center', marginBottom: '15px' } }
				onClick={ () => {
					this.setState( {
						isSettingsOpen: true,
						componentTitle: 'Add/Edit Content Controls',
						updateProp: 'controls',
					} );
				} }
			>Add/Edit Content Controls</Button>
			<Button
				variant="secondary"
				type="button"
				size="compact"
				style={ { width: '100%', boxSizing: 'border-box', justifyContent: 'center' } }
				onClick={ () => {
					this.setState( { 
						isSettingsOpen: true,
						componentTitle: 'Add/Edit Style Controls',
						updateProp: 'styles',
					} );
				} }
			>Add/Edit Style Controls</Button>
			{ this.state.isSettingsOpen && (
				<Modal
					title={ this.state.componentTitle }
					size="large"
					headerActions={ this.saveButton() }
					onRequestClose={ () => {
						this.setState( { isSettingsOpen: false } )
					} }
				>
					<JetEngineRepeater
						data={ this.state.settings[ this.state.updateProp ] }
						default={ this.getDefaultItem() }
						onChange={ newData => {
							this.updateSettingsState( {
								[ this.state.updateProp ]: newData
							} );
						} }
					>
						{
							( item ) => <div>
								<TextControl
									type="text"
									label={ __( 'Control Label' ) }
									help={ __( 'Control label to show in the component UI in editor' ) }
									value={ item.control_label }
									onChange={ newValue => {
										this.updateItem( item, 'control_label', newValue )
									} }
								/>
								<TextControl
									type="text"
									label={ __( 'Control Name' ) }
									help={ __( 'Control key/name to save into the DB. Please use only lowercase letters, numbers and `_`. Also please note - name must be unique for this component (for both - styles and controls)' ) }
									value={ item.control_name }
									onChange={ newValue => {
										this.updateItem( item, 'control_name', newValue )
									} }
								/>
								{ ( 'controls' == this.state.updateProp ) && <SelectControl
									label={ __( 'Control Type' ) }
									help={ __( 'Type of control for UI' ) }
									value={ item.control_type }
									options={ this.props.controlTypes }
									onChange={ newValue => {
										this.updateItem( item, 'control_type', newValue )
									} }
								/> }
								{ ( undefined !== item.control_options ) && this.isControlVisible( 'control_options', item ) && <TextareaControl
									type="text"
									label={ __( 'Options' ) }
									help={ __( 'One option per line. Split label and value with `::`, for example - red::Red' ) }
									value={ item.control_options }
									onChange={ newValue => {
										this.updateItem( item, 'control_options', newValue )
									} }
								/> }
								{ this.isControlVisible( 'control_default', item ) && <TextareaControl
									type="text"
									label={ __( 'Default Value' ) }
									help={ __( 'Default value of the given control' ) }
									value={ item.control_default }
									onChange={ newValue => {
										this.updateItem( item, 'control_default', newValue )
									} }
								/> }
								{ ( undefined !== item.control_default_image ) && this.isControlVisible( 'control_default_image', item ) && <div className="jet-media-control components-base-control">
									<div className="components-base-control__label">Default Image</div>
									<Flex
										align="flex-start"
									>
										<FlexItem>
											<MediaUpload
												onSelect={ ( media ) => {

													let imgURL;
													let imgThumb;

													imgURL = media.sizes.full.url;

													if ( media.sizes.thumbnail ) {
														imgThumb = media.sizes.thumbnail.url;
													} else {
														imgThumb = media.sizes.full.url;
													}

													this.updateItem( item, 'control_default_image', {
														id: media.id,
														url: imgURL,
														thumb: imgThumb
													} );
												} }
												type="image"
												value={ item.control_default_image.id }
												render={ ( { open } ) => (
													<Button
														isSecondary
														icon="edit"
														onClick={ open }
													>{ __( "Select Image" ) }</Button>
												)}
											/>
											{ item.control_default_image.id &&
												<div>
													<Button
														style={ { marginTop: '5px' } }
														onClick={ () => {
															this.updateItem( item, 'control_default_image', false );
														} }
														isLink
														isDestructive
													>
														{ __( 'Clear' ) }
													</Button>
												</div>
											}
										</FlexItem>
										<FlexItem>
										{ item?.control_default_image?.thumb &&
											<img src={ item.control_default_image.thumb } width="80px" height="auto" />
										}
										</FlexItem>
									</Flex>
								</div> }
							</div>
						}
					</JetEngineRepeater>
				</Modal>
			) }
		</div> );
	}

}

// Export the SettingsApp component for use in other parts of the application.
export default SettingsApp;