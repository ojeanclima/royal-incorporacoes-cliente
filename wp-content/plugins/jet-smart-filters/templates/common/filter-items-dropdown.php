<?php

if ( empty( $args ) ) {
	return;
}

$dropdown_apply_button      = $args['dropdown_apply_button'];
$dropdown_apply_button_text = $args['dropdown_apply_button_text'];

// dropdown N selected
$data_dropdown_n_selected      = '';
$data_dropdown_n_selected_text = '';
if ( ! empty( $args['dropdown_n_selected_enabled'] ) ) {
	$data_dropdown_n_selected      = 'data-dropdown-n-selected="' . $args['dropdown_n_selected_number'] . '"';
	$data_dropdown_n_selected_text = 'data-dropdown-n-selected-text="' . $args['dropdown_n_selected_text'] . '"';
}

?>
<div class="jet-filter-items-dropdown"
	 <?php echo $data_dropdown_n_selected ?>
	 <?php echo $data_dropdown_n_selected_text ?>>
	<div class="jet-filter-items-dropdown__label" <?php echo jet_smart_filters()->data->get_tabindex_attr(); ?>><?php echo isset( $args['dropdown_placeholder'] ) ? $args['dropdown_placeholder'] : '' ?></div>
	<div class="jet-filter-items-dropdown__body">
		<?php include jet_smart_filters()->get_template( 'filters/' . $this->filter_type . '.php' ); ?>
		<?php if ( $dropdown_apply_button ) : ?>
		<div class="jet-filter-items-dropdown__footer" >
			<button type="button" class="jet-filter-items-dropdown__apply-button"
				<?php echo jet_smart_filters()->data->get_tabindex_attr(); ?>
			><?php echo $dropdown_apply_button_text; ?></button>
		</div>
		<?php endif; ?>
	</div>
</div>