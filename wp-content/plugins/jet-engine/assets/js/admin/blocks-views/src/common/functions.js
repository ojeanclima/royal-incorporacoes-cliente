function isIdUnique( props ) {
	const blockEditor = wp.data.select('core/block-editor');
	const clientIds = blockEditor.getBlocksByName( props.name );
	const blocks = blockEditor.getBlocksByClientId( clientIds );

	return !blocks.some( ( block ) => block.clientId !== props.clientId && block.attributes._block_id === props.attributes._block_id );
}

export { isIdUnique };
