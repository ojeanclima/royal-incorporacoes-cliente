<?php
/**
 * Checkbox list item template
 */

$image_src  = wp_get_attachment_image_src( $image, $display_options['filter_image_size'] );
$image_alt  = get_post_meta( $image, '_wp_attachment_image_alt', true );
$show_label = $display_options['show_items_label'];

if ( ! empty( $image_src[0] ) ) {
	$image_src = $image_src[0];
} else {
	$image_src = jet_smart_filters()->plugin_url( 'assets/images/placeholder.png' );
}

if ( ! $image_alt ) {
	$image_alt = $label;
}

?>
<div class="jet-color-image-list__row jet-filter-row<?php echo $extra_classes; ?>">
	<label class="jet-color-image-list__item" <?php echo jet_smart_filters()->data->get_tabindex_attr(); ?>>
		<input
			type="<?php echo $filter_type; ?>"
			class="jet-color-image-list__input"
			name="<?php echo $query_var; ?>"
			value="<?php echo $value; ?>"
			data-label="<?php echo $label; ?>"
			<?php if ( ! empty( $data_attrs ) ) {
				echo jet_smart_filters()->utils->generate_data_attrs( $data_attrs );
			} ?>
			aria-label="<?php echo $label; ?>"
			<?php echo $checked; ?>
		>
		<div class="jet-color-image-list__button">
			<?php /* all decorator */ if ( 'all' === $value ) : ?>
				<?php if ( $image_src ) : ?>
					<span class="jet-color-image-list__decorator">
						<span class="jet-color-image-list__image"><img src="<?php echo $image_src; ?>" alt="<?php echo $image_alt ?>"></span>
					</span>
				<?php endif; ?>
			<?php /* default decorator */ else : ?>
				<span class="jet-color-image-list__decorator">
					<?php if ( 'color' === $type ) : ?>
						<span class="jet-color-image-list__color" style="background-color: <?php echo $color ?>"></span>
					<?php endif; ?>
					<?php if ( 'image' === $type ) : ?>
						<span class="jet-color-image-list__image"><img src="<?php echo $image_src; ?>" alt="<?php echo $image_alt ?>"></span>
					<?php endif; ?>
				</span>
			<?php endif; ?>
			<?php /* label */ if ( $show_label ) : ?>
				<span class="jet-color-image-list__label"><?php echo $label; ?></span>
			<?php endif; ?>
			<?php do_action('jet-smart-filter/templates/counter', $args ); ?>
		</div>
	</label>
</div>