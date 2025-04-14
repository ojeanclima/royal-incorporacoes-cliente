<?php
/**
 * Filter items search template
 */

if ( empty( $args['search_enabled'] ) ) {
	return;
}

$search_placeholder = $args['search_placeholder'];

?>
<div class="jet-filter-items-search">
	<input
		class="jet-filter-items-search__input"
		type="search"
		autocomplete="off"
		aria-label="<?php printf( __( 'Search in %s', 'jet-smart-filters' ), $accessibility_label ); ?>"
		<?php echo $search_placeholder ? 'placeholder="' . $search_placeholder .'"' : '' ?>
	>
	<div class="jet-filter-items-search__clear">
		<?php echo jet_smart_filters()->print_template( 'svg/close.svg' ); ?>
	</div>
</div>