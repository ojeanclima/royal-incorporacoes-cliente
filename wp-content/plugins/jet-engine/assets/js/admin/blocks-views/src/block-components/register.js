const {
	CustomControl
} = window.JetEngineBlocksComponents;

const {
	registerBlockType
} = wp.blocks;

const {
	InspectorControls,
	MediaUpload,
	RichText
} = wp.blockEditor;

const {
	PanelBody,
	Disabled,
	Button
} = wp.components;

const {
	serverSideRender: ServerSideRender
} = wp;

const {
	useState,
	Fragment
} = wp.element;

if ( window.JetEngineListingData.blockComponents ) {
	for ( var i = 0; i < window.JetEngineListingData.blockComponents.length; i++)  {
		
		const blockComponent = window.JetEngineListingData.blockComponents[ i ];

		registerBlockType( blockComponent.name, {
			title: blockComponent.title,
			icon: <svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M31.2924 4.7072C31.6829 4.31668 32.3161 4.31668 32.7066 4.7072L43.922 15.9226C44.3125 16.3132 44.3125 16.9463 43.922 17.3369L32.7066 28.5523C32.3161 28.9428 31.6829 28.9428 31.2924 28.5523L20.077 17.3369C19.6864 16.9463 19.6864 16.3132 20.077 15.9226L31.2924 4.7072ZM22.1983 16.6297L31.9995 6.82852L41.8007 16.6297L31.9995 26.431L22.1983 16.6297Z"/><path fill-rule="evenodd" clip-rule="evenodd" d="M15.9223 20.0773C16.3128 19.6868 16.9459 19.6868 17.3365 20.0773L28.5519 31.2928C28.9424 31.6833 28.9424 32.3164 28.5519 32.707L17.3365 43.9224C16.9459 44.3129 16.3128 44.3129 15.9223 43.9224L4.70683 32.707C4.31631 32.3164 4.31631 31.6833 4.70683 31.2928L15.9223 20.0773ZM6.82815 31.9999L16.6294 22.1986L26.4306 31.9999L16.6294 41.8011L6.82815 31.9999Z"/><path fill-rule="evenodd" clip-rule="evenodd" d="M48.0772 20.0773C47.6867 19.6868 47.0535 19.6868 46.663 20.0773L35.4476 31.2928C35.057 31.6833 35.057 32.3164 35.4476 32.707L46.663 43.9224C47.0535 44.3129 47.6867 44.3129 48.0772 43.9224L59.2926 32.707C59.6831 32.3164 59.6831 31.6833 59.2926 31.2928L48.0772 20.0773ZM47.3701 22.1986L37.5689 31.9999L47.3701 41.8011L57.1713 31.9999L47.3701 22.1986Z"/><path fill-rule="evenodd" clip-rule="evenodd" d="M31.2924 35.4479C31.6829 35.0574 32.3161 35.0574 32.7066 35.4479L43.922 46.6634C44.3125 47.0539 44.3125 47.687 43.922 48.0776L32.7066 59.293C32.3161 59.6835 31.6829 59.6835 31.2924 59.293L20.077 48.0776C19.6864 47.687 19.6864 47.0539 20.077 46.6634L31.2924 35.4479ZM22.1983 47.3705L31.9995 37.5692L41.8007 47.3705L31.9995 57.1717L22.1983 47.3705Z"/></svg>,
			attributes: blockComponent.attributes,
			category: 'jet-engine',
			usesContext: [ 'postId', 'postType', 'queryId' ],
			edit( props ) {

				const attributes = props.attributes;
				const [ editRichText, setEditRichText] = useState(false);


				var object = window.JetEngineListingData.object_id;
				var listing = window.JetEngineListingData.settings;

				if ( props.context.queryId ) {
					object  = props.context.postId;
					listing = {
						listing_source: 'posts',
						listing_post_type: props.context.postType,
					};
				}

				return [
					props.isSelected && (
						<InspectorControls
							key={ 'inspector' }
						>
							<PanelBody title={ 'General' }>
								{ blockComponent.attributes 
								&& Object.keys( blockComponent.attributes ).length
								&& Object.keys( blockComponent.attributes ).map( ( attr ) => {

									const control = blockComponent.attributes[ attr ].controlType;

									return <CustomControl
										control={ control }
										value={ attributes[ attr ] }
										getValue={ ( name ) => {
											return attributes[ name ];
										} }
										onChange={ newValue => {
											props.setAttributes( { [ attr ]: newValue } );
										} }
										onRichTextEdit={ ( control ) => {
											setEditRichText( { ...{
												name: attr,
												value: attributes[ attr ]
											}, ...control } );
										} }
									/>;
								} ) }
							</PanelBody>
						</InspectorControls>
					),
					<Fragment>
						{ false === editRichText && <Disabled key={ 'block_render' }>
							<ServerSideRender
								block={ blockComponent.name }
								attributes={ attributes }
								urlQueryArgs={ {
									object: object,
									listing: listing,
									is_component_preview: 1,
								} }
							/>
						</Disabled> }
						{ false !== editRichText && <Fragment>
							<RichText
								tagName="div"
								value={ editRichText.value }
								placeholder={ "Set " + editRichText.label }
								onChange={ ( newValue ) => {
									props.setAttributes( { [ editRichText.name ]: newValue } );
								} }
								isSelected={ true }
								toolbar={[
									'bold',
									'italic',
									'link',
									'heading',
								]}
							/>
							<Button
								isSecondary
								icon="saved"
								size="compact"
								style={{
									margin: "10px 0 0",
									paddingRight: "15px"
								}}
								onClick={ () => {
									setEditRichText( false )
								} }
							>Done</Button>
						</Fragment> }
					</Fragment>
				];
			},
			save: props => {
				return null;
			}
		} );

	}
}
