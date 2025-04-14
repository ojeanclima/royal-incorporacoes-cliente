<?php
/**
 * Collapsible toggle element template
 */
?>
<?php if ( $is_parent ): ?>
<div class="jet-collapse-icon">
	<svg class="jet-collapse-icon-open" viewBox="0 0 24 24" fill="none">
		<path d="M6 12H18M12 6V18" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
	</svg>
	<svg class="jet-collapse-icon-close" viewBox="0 0 24 24" fill="none">
		<path d="M6 12L18 12" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
	</svg>
</div>
<?php else: ?>
<div class="jet-collapse-none"></div>
<?php endif; ?>
