const { __ } = wp.i18n;
const {
	registerBlockType
} = wp.blocks;

const {
	ColorPalette,
	RichText,
	Editable,
	MediaUpload
} = wp.editor;

const {
	InspectorControls,
} = wp.blockEditor;

const {
	IconButton,
	TextControl,
	SelectControl,
	ToggleControl,
	PanelBody,
	Disabled,
	G,
	Path,
	Circle,
	Rect,
	SVG
} = wp.components;

const {
	serverSideRender: ServerSideRender
} = wp;

const GIcon = <SVG xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64" fill="none"><Rect fill="white" x="1" y="5" width="44" height="53" rx="3" stroke="#162B40" strokeWidth="2"></Rect><Path d="M7 49C7 47.3431 8.34315 46 10 46H21C22.6569 46 24 47.3431 24 49C24 50.6569 22.6569 52 21 52H10C8.34315 52 7 50.6569 7 49Z" fill="#4AF3BA" stroke="#162B40" strokeWidth="2"></Path><Rect fill="white" x="7" y="33" width="32" height="6" rx="1" stroke="#162B40" strokeWidth="2"></Rect><Rect fill="white" x="7" y="23" width="32" height="6" rx="1" stroke="#162B40" strokeWidth="2"></Rect><Rect fill="white" x="6.5" y="14.5" width="18" height="1" rx="0.5" stroke="#162B40"></Rect><Rect fill="white" x="6.5" y="10.5" width="33" height="1" rx="0.5" stroke="#162B40"></Rect></SVG>;

const blockAttributes = window.JetEngineListingData.atts.dynamicTable;

registerBlockType( 'jet-engine/dynamic-table', {
	title: __( 'Dynamic Table' ),
	icon: GIcon,
	category: 'layout',
	attributes: blockAttributes,
	className: 'jet-table',
	edit: class extends wp.element.Component {
		render() {

			const props      = this.props;
			const attributes = props.attributes;
			const tablesList = window.JetEngineListingData.tablesList;

			return [
				props.isSelected && (
					<InspectorControls
						key={ 'inspector' }
					>
						<PanelBody title={ __( 'General' ) }>
							<SelectControl
								label={ __( 'Select table' ) }
								value={ attributes.table_id }
								options={ tablesList }
								onChange={ newValue => {
									props.setAttributes( { table_id: newValue } );
								} }
							/>
							<ToggleControl
								label={ __( 'Show column names in table header' ) }
								checked={ attributes.thead }
								onChange={ () => {
									props.setAttributes( { thead: ! attributes.thead } );
								} }
							/>
							<ToggleControl
								label={ __( 'Show column names in table footer' ) }
								checked={ attributes.tfoot }
								onChange={ () => {
									props.setAttributes( { tfoot: ! attributes.tfoot } );
								} }
							/>
							<ToggleControl
								label={ __( 'Allow horizontal sroll' ) }
								checked={ attributes.scrollable }
								onChange={ () => {
									props.setAttributes( { scrollable: ! attributes.scrollable } );
								} }
							/>
							<ToggleControl
								label={ __( 'Rewrite table query' ) }
								help={ __( 'Use deffirent query. Allow to use different data for same layout and avoid tables duplicating' ) }
								checked={ attributes.rewrite_query }
								onChange={ () => {
									props.setAttributes( { rewrite_query: ! attributes.rewrite_query } );
								} }
							/>
							{ attributes.rewrite_query && <SelectControl
								label={ __( 'New Query' ) }
								value={ attributes.rewrite_query_id }
								options={ window.JetEngineListingData.queriesList }
								onChange={ newValue => {
									props.setAttributes( { rewrite_query_id: newValue } );
								} }
							/> }
						</PanelBody>
					</InspectorControls>
				),
				<Disabled key={ 'block_render' }>
					<ServerSideRender
						block="jet-engine/dynamic-table"
						attributes={ attributes }
					/>
				</Disabled>
			];
		}
	},
	save: props => {
		return null;
	}
} );
