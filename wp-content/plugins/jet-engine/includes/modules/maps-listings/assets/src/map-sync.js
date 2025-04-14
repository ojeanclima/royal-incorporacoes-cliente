const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

const {
	SVG,
	Path
} = wp.primitives;

const {
	InspectorControls
} = wp.editor;

const {
	PanelBody,
	SelectControl,
	TextControl,
	ToggleControl
} = wp.components;

const {
	RepeaterControl,
	CustomControl
} = window.JetEngineBlocksComponents;

const Icon = <SVG version="1.1" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 1024 1024"><Path d="M528 576c-88.365 0-160-71.635-160-160s71.635-160 160-160c88.365 0 160 71.635 160 160s-71.635 160-160 160zM528 540.363c68.685 0 124.363-55.678 124.363-124.363s-55.678-124.363-124.363-124.363c-68.685 0-124.363 55.678-124.363 124.363s55.678 124.363 124.363 124.363zM652.749 798.691c24.386 57.206 81.136 97.309 147.251 97.309 82.965 0 151.182-63.147 159.21-144h-32.2c-7.874 63.141-61.736 112-127.010 112-70.693 0-128-57.307-128-128s57.307-128 128-128c40.422 0 76.469 18.738 99.926 48h-35.926v32h96v-96h-32v47.984c-29.192-38.853-75.661-63.984-128-63.984-3.539 0-7.050 0.115-10.531 0.341 25.792-56.992 42.531-112.067 42.531-156.648 0-169.934-136.106-307.693-304-307.693s-304 137.758-304 307.693c0 169.933 243.2 492.307 304 492.307 23.070 0 72.405-46.418 124.749-113.309zM796.363 419.693c0 35.168-12.918 81.819-35.946 134.293-4.562 10.395-9.466 20.896-14.659 31.443-61.669 22.219-105.758 81.246-105.758 150.571 0 6.478 0.386 12.866 1.133 19.141-22.925 30.875-45.558 58.042-65.774 79.24-14.898 15.626-27.774 27.259-37.918 34.701-3.989 2.926-7.128 4.888-9.44 6.133-2.312-1.245-5.451-3.206-9.44-6.133-10.144-7.442-23.021-19.075-37.918-34.701-29.654-31.098-64.514-75.034-97.859-124.68-33.32-49.606-64.491-103.968-87.2-155.715-23.027-52.474-35.946-99.125-35.946-134.293 0-150.659 120.555-272.056 268.363-272.056s268.363 121.397 268.363 272.056zM524.805 876.701c-0.072 0.019-0.109 0.032-0.109 0.032l0.062-0.018 0.046-0.014zM80 656c-8.836 0-16 7.163-16 16s7.163 16 16 16h192c8.837 0 16-7.163 16-16s-7.163-16-16-16h-192zM80 720c-8.836 0-16 7.163-16 16s7.163 16 16 16h192c8.837 0 16-7.163 16-16s-7.163-16-16-16h-192zM80 784c-8.836 0-16 7.163-16 16s7.163 16 16 16h192c8.837 0 16-7.163 16-16s-7.163-16-16-16h-192z"></Path></SVG>;

registerBlockType( 'jet-smart-filters/map-sync', {
	title: __( 'Map Sync' ),
	icon: Icon,
	category: 'jet-smart-filters',
	supports: {
		html: false
	},
	attributes: {
		// General
		query_id: {
			type: 'string',
			default: '',
		},
		content_provider: {
			type: 'string',
			default: 'not-selected',
		},
		additional_providers_enabled: {
			type: 'bool',
			default: false,
		},
		additional_providers_list: {
			type: 'array',
			default: [
				{
					additional_provider: '',
					additional_query_id: ''
				}
			],
		},
	},
	className: 'jet-smart-filters-map-sync',
	edit: class extends wp.element.Component {

		getOptionsFromObject( object ) {

			const result = [];

			for ( const [ value, label ] of Object.entries( object ) ) {
				result.push( {
					value: value,
					label: label,
				} );
			}

			return result;

		}

		render() {
			
			const props = this.props;

			const repeaterControls = [
				{
					label: 'Provider',
					name: 'additional_provider',
					type: 'select',
					options: this.getOptionsFromObject( window.JetSmartFilterBlocksData.mapSyncProviders ),
				},
				{
					label: 'Query ID',
					name: 'additional_query_id',
					type: 'text'
				}
			];

			return [
				props.isSelected && (
					<InspectorControls
						key={'inspector'}
					>
						<PanelBody title={__( 'General' )}>
							<div>
								<h4 style={{margin:'5px 0 0'}}>Please note!</h4>
								<p style={{ color: '#757575', fontSize: '12px' }}>
									This filter is compatible only with queries from JetEngine Query Builder. ALso you need to set up <a href="https://crocoblock.com/knowledge-base/jetengine/how-to-set-geo-search-based-on-user-geolocation/" target="_blank">Geo Query</a> in your query settings to make the filter work correctly.
								</p>
							
								<h4 style={{margin:'5px 0 0'}}>This filter for: Map Listing.</h4>
								<p style={{ color: '#757575', fontSize: '12px' }}>
									<i>Map Sync filter does not work if there is no Map Listing on the page, and that Map Listing is a main provider.</i>
								</p>
							</div>
							<TextControl
								type="text"
								label={ __( 'Query ID' ) }
								help={ __( 'Map Listing query ID. Set unique query ID if you use multiple Map Listings on the page. Same ID you need to set for filtered block.' ) }
								value={ props.attributes.query_id }
								onChange={ newValue => {
									props.setAttributes( { query_id: newValue } );
								} }
							/>

							<ToggleControl
								label={ __( 'Additional Providers Enabled' ) }
								checked={ props.attributes.additional_providers_enabled }
								onChange={ () => {
									props.setAttributes({ additional_providers_enabled: ! props.attributes.additional_providers_enabled });
								} }
							/>

							{ props.attributes.additional_providers_enabled && <RepeaterControl
								data={ props.attributes?.additional_providers_list || [] }
								default={ {} }
								onChange={ newData => {
									props.setAttributes( { additional_providers_list: newData } );
								} }
							>
								{
									( item, index ) =>
										<div>
										{ repeaterControls.map( ( control ) => {

											const setValue = ( newValue ) => {
																			
												const additionalProviders  = [ ...props.attributes.additional_providers_list ];
												const currentItem = additionalProviders[ index ];

												if ( ! currentItem ) {
													return;
												}

												additionalProviders[ index ] = _.assign( {}, currentItem, {
													[ control.name ]: newValue
												} );

												props.setAttributes( { additional_providers_list: [ ...additionalProviders ] } );

											}

											return <CustomControl
												control={ control }
												value={ item[ control.name ] }
												getValue={ ( key, attr, object ) => {

													object = object || {};

													if ( ! key || ! attr ) {
														return '';
													}

													if ( ! object[ key ] ) {
														return '';
													}

													return object[ key ];
												} }
												attr={ control.name }
												attributes={ item }
												onChange={ newValue => {
													setValue( newValue );
												} }
											>
											</CustomControl>
											} ) }
										</div>
								}
							</RepeaterControl> }

						</PanelBody>
					</InspectorControls>
				)
			];

		}

	},
	save: props => {
		return null;
	},
} );
