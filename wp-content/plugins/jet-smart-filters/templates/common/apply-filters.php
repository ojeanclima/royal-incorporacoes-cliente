<?php
/**
 * Apply filters button
 */

$show_apply_button = ( ! empty( $settings['apply_on'] ) && $settings['apply_on'] === 'submit' ) && ! empty( $settings['apply_button'] )
	? filter_var( $settings['apply_button'], FILTER_VALIDATE_BOOLEAN )
	: false;

if ( ! $show_apply_button ) {
	return;
}

$apply_button_text = $settings['apply_button_text'];

if ( empty( $apply_button_text ) ) {
	return;
}

$btn_classes = "apply-filters__button";

$active_state = ! empty( $settings['active_state'] ) && $settings['active_state'] !== 'always' ? $settings['active_state'] : false;
$if_inactive  = ! empty( $settings['if_inactive'] ) ? $settings['if_inactive'] : 'disable';

if ( $active_state ) {
	if ($if_inactive === 'hide') {
		$btn_classes .= " jsf_hidden";
	} else {
		$btn_classes .= " jsf_disabled";
	}
}

?>
<div class="apply-filters"<?php if ( ! empty( $data_atts ) ) {
	echo ' ' . $data_atts;
} ?>>
	<button
		type="button"
		class="<?php echo $btn_classes; ?>"
		<?php if ( $active_state ): ?>
			data-active-state="<?php echo $active_state; ?>"
			data-if-inactive="<?php echo $if_inactive; ?>"
			<?php if ( $if_inactive === "disable" ): ?>
				disabled
			<?php endif; ?>
		<?php endif; ?>
		<?php echo jet_smart_filters()->data->get_tabindex_attr(); ?>
	><?php echo $apply_button_text; ?></button>
</div>