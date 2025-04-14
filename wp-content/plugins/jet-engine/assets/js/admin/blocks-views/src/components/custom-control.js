import GroupedSelectControl from "components/grouped-select-control.js";

const {
	SelectControl,
	ToggleControl,
	TextControl,
	TextareaControl,
	ColorPalette,
	BaseControl,
	Button,
	Flex,
	FlexBlock,
	FlexItem,
	ToolbarGroup,
	ToolbarButton
} = wp.components;

const {
	MediaUpload,
	MediaUploadCheck
} = wp.blockEditor;

const {
	Component,
	Fragment
} = wp.element;

class CustomControl extends Component {

	isEnabled() {

		if ( ! this.props.condition ) {
			return true;
		}

		for ( var field in this.props.condition ) {

			var compare        = this.props.condition[ field ];
			var checked        = true;
			var isNotEqualCond = field.includes( '!' );

			if ( isNotEqualCond ) {
				field = field.replace( '!', '' );
			}

			if ( this.props.prefix ) {
				field = this.props.prefix + field;
			}

			var fieldVal = this.props.getValue( field, this.props.attr, this.props.attributes );

			if ( isNotEqualCond ) {
				if ( Array.isArray( compare ) ) {
					checked = ! compare.includes( fieldVal );
				} else {
					checked = fieldVal != compare;
				}
			} else {
				if ( Array.isArray( compare ) ) {
					checked = compare.includes( fieldVal );
				} else {
					checked = fieldVal == compare;
				}
			}

			if ( ! checked ) {
				return false;
			}

		}

		return true;
	}

	htmlDesc( htmlDescription ) {
		return ( htmlDescription && <p
			className="components-base-control__help"
			style={ {
				fontSize: '12px',
				fontStyle: 'normal',
				color: 'rgb(117, 117, 117)',
				margin: '-7px 0 20px',
			} }
			dangerouslySetInnerHTML={{ __html: htmlDescription }}
		></p> );
	}

	render() {

		const {
			control,
			value,
			onChange,
			onRichTextEdit,
			children
		} = this.props;

		if ( ! this.isEnabled() ) {
			return null;
		}

		let htmlDescription = ( control.has_html && control.description ) ? control.description : '';
		let description     = ( ! htmlDescription && control.description  ) ? control.description : '';

		const uid = Math.floor( Math.random() * 89999 ) + 10000;

		switch ( control.type ) {

			case 'select':
			case 'select2':

				let options = [];

				if ( control.options && control.options.length ) {

					options = [ ...control.options ];

					if ( control.placeholder ) {
						options.unshift( {
							value: '',
							label: control.placeholder,
						} );
					}

				}

				if ( control.groups ) {
					return <Fragment>
						{ children }
						<GroupedSelectControl
							label={ control.label }
							help={ description }
							options={ control.groups }
							value={ value }
							onChange={ newValue => {
								onChange( newValue );
							} }
						/>
						{ this.htmlDesc( htmlDescription ) }
					</Fragment>;
				} else {
					return <Fragment>
						{ children }
						<SelectControl
							label={ control.label }
							help={ description }
							options={ options }
							value={ value }
							onChange={ newValue => {
								onChange( newValue );
							} }
						/>
						{ this.htmlDesc( htmlDescription ) }
					</Fragment>;
				}

			case 'rich_text':
				return <Fragment>
					{ children }
					<div>
						<label>{ control.label }</label>
					</div>
					<Button
						isSecondary
						icon="edit"
						size="small"
						style={{margin: "5px 0 5px"}}
						onClick={ () => {
							onRichTextEdit( control );
						} }
					>Edit HTML</Button>
					<div><small>* Opens in component body</small></div>
					<div>{ this.htmlDesc( htmlDescription ) }</div>
				</Fragment>;
			case 'textarea':
				return <Fragment>
					{ children }
					<TextareaControl
						label={ control.label }
						help={ description }
						value={ value }
						onChange={ newValue => {
							onChange( newValue );
						} }
					/>
					{ this.htmlDesc( htmlDescription ) }
				</Fragment>;

			case 'switcher':
				return <Fragment>
					{ children }
					<ToggleControl
						label={ control.label }
						help={ description }
						checked={ value }
						onChange={ () => {
							onChange( !value );
						} }
					/>
					{ this.htmlDesc( htmlDescription ) }
				</Fragment>;

			case 'number':
				return <Fragment>
					{ children }
					<TextControl
						type="number"
						label={ control.label }
						help={ description }
						min={ control.min ? control.min : 1 }
						max={ control.max ? control.max : 100 }
						step={ control.step ? control.step : 1 }
						value={ value }
						onChange={ newValue => {
							onChange( Number( newValue ) );
						} }
					/>
					{ this.htmlDesc( htmlDescription ) }
				</Fragment>;

			case 'raw_html':
				return <Fragment>
					{ children }
					<p
						dangerouslySetInnerHTML={{ __html: control.raw }}
					></p>
				</Fragment>;

			case 'color':

				const colorPalette = wp.data.select( 'core/block-editor' ).getSettings().colors;

				return <BaseControl
						label={ control.label }
						id={ 'color_label_' + uid }
					>
					<ColorPalette
						colors={ colorPalette }
						value={ value }
						ariaLabel={ control.label }
						id={ 'color_label_' + uid }
						onChange={ newValue => {
							onChange( newValue );
						} }
					/>
				</BaseControl>;

			case 'media':

				const mediaId = value.id || false;

				return <BaseControl
					label={ control.label }
					id={ 'media_label_' + uid }
				>
					<Flex
						align="flex-start"
					>
						<FlexItem>
							<MediaUploadCheck>
								<MediaUpload
									onSelect={ ( media ) => {
										onChange( {
											id: media.id,
											url: media.url,
											thumb: media.sizes.thumbnail.url
										} );
									} }
									type="image"
									value={ value.id || false }
									render={ ( { open } ) => (
										<Button
											isSecondary
											icon="edit"
											onClick={ open }
										>Select Image</Button>
									)}
								/>
							</MediaUploadCheck>
							{ undefined !== value.id &&
								<div>
									<Button
										style={ { marginTop: '5px' } }
										onClick={ () => {
											onChange( { id: false } );
										} }
										isLink
										isDestructive
									>
										Clear
									</Button>
								</div>
							}
						</FlexItem>
						<FlexItem>
						{ undefined !== value.thumb &&
							<img src={ value.thumb } width="80px" height="auto" />
						}
						</FlexItem>
					</Flex>
				</BaseControl>;

			default:
				return <Fragment>
					{ children }
					<TextControl
						type="text"
						label={ control.label }
						help={ description }
						value={ value }
						onChange={ newValue => {
							onChange( newValue );
						} }
					/>
					{ this.htmlDesc( htmlDescription ) }
				</Fragment>;
		}
	}
}

window.JetEngineBlocksComponents = window.JetEngineBlocksComponents || {};
window.JetEngineBlocksComponents.CustomControl = CustomControl;

export default CustomControl;
