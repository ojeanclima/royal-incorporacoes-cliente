import EditMeta from 'related-items-table-edit-meta';
import RowActions from 'related-items-table-row-actions';
import {
	DragDropContext,
	Droppable,
	Draggable
} from 'react-beautiful-dnd';

const {
	Button
} = wp.components;

const {
	Component,
	Fragment
} = wp.element;

class RelatedItemsTable extends Component {

	constructor( props ) {
		super( props );
		this.state = {
			reorderedItems: false,
		}
	}

	hasMeta() {
		return ( this.props.metaFields && 0 < this.props.metaFields.length );
	}

	onDragEnd( result ) {

		const fromIndex = result.source.index;
		const toIndex   = result.destination.index;
		const items     = [ ...this.items() ];
		const [ item ]  = items.splice( fromIndex, 1 );

		items.splice( toIndex, 0, item );

		this.setState( { reorderedItems: items } );
	}

	items() {
		return this.state.reorderedItems || this.props.items;
	}

	disableOrder() {
		return false === window.JetEngineRelationsCommon.orderMode ? true : false;
	}

	render() {

		const relatedItemsList = this.items().map( ( item, rIndex ) => {

			let row = item.columns.map( ( col, index ) => {
				return ( <td key={ 'col_' + index } dangerouslySetInnerHTML={ { __html: col } }></td> );
			} );

			return ( <Draggable
				key={ item._ID }
				draggableId={ item._ID }
				index={ rIndex }
				isDragDisabled={ this.disableOrder() }
			>
				{ ( provided, snapshot ) => (
					<tr
						key={ 'row_' + item._ID }
						ref={ provided.innerRef }
						{ ...provided.draggableProps }
						{ ...provided.dragHandleProps }
						className={ snapshot.isDragging ? 'jet-engine-rels__dragging-row' : '' }
					>
						{ row }
						{ this.hasMeta() && <td className="rel-meta">
							<EditMeta
								{ ...this.props }
								relatedObjectID={ item.related_id }
							/>
						</td> }
						<td>
							<RowActions
								actions={ item.actions }
								relID={ this.props.relID }
								relatedObjectID={ item.related_id }
								relatedObjectType={ this.props.controlObjectType }
								relatedObjectName={ this.props.controlObjectName }
								currentObjectID={ this.props.currentObjectID }
								isParentProcessed={ this.props.isParentProcessed }
								onUpdate={ ( items ) => {
									this.props.onUpdate( items );
								} }
							/>
						</td>
					</tr>
				) }
			</Draggable> );
		} );

		const columnsHeadings = this.props.columns.map( ( item ) => {
			return ( <th key={ 'rel-heading-' + item.key } className={ 'rel-' + item.key }>{ item.label }</th> );
		} );

		return ( <div className="jet-engine-rels__table-wrap">
			{ false !== this.state.reorderedItems && 0 < this.props.items.length && <div class="jet-engine-rels__was-reordered">
				<a
					href="#"
					className="jet-engine-rels__was-reordered-accept"
					onClick={ ( event ) => {
						event.preventDefault();
						this.props.onReorder( this.state.reorderedItems, () => {
							this.setState( { reorderedItems: false } );
						} );
					} }
				>Save order</a>
				<a
					href="#"
					className="jet-engine-rels__was-reordered-cancel"
					onClick={ ( event ) => {
						event.preventDefault();
						this.setState( { reorderedItems: false } );
					} }
				>Cancel</a>
			</div> }
			<DragDropContext onDragEnd={ ( result ) => {
				this.onDragEnd( result );
			} }>
				<table className="wp-list-table widefat fixed striped table-view-list jet-engine-rels__table">
					<thead>
						<tr>{ columnsHeadings }</tr>
					</thead>
					<Droppable droppableId="relateditems" type="ROWS">
						{ ( provided ) => (
							<tbody
								{ ...provided.droppableProps }
								ref={ provided.innerRef }
							>
								{ 0 < this.props.items.length && relatedItemsList }
								{ ! this.props.items.length && <tr>
									<td colSpan={ this.props.columns.length }>{ '--' }</td>
								</tr> }
								{ provided.placeholder }
							</tbody>
						) }
					</Droppable>
					<tfoot>
						<tr>{ columnsHeadings }</tr>
					</tfoot>
				</table>
			</DragDropContext>
		</div> );
	}

}

export default RelatedItemsTable;
