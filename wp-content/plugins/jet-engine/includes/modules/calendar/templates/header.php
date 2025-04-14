<?php
/**
 * Calendar header template
 * @var Jet_Listing_Render_Calendar $this
 */

global $wp_locale;

$allow_select     = filter_var( $settings['allow_date_select'] ?? false, FILTER_VALIDATE_BOOLEAN );
$hide_past_events = filter_var( $settings['hide_past_events'] ?? false, FILTER_VALIDATE_BOOLEAN );

if ( $allow_select ) {
	if ( $hide_past_events ) {
		$start_year = wp_date( 'Y' );
	} else {
		$start_year = ! empty( $settings['start_year_select'] ) ? $settings['start_year_select'] : 1970;
		$start_year = jet_engine()->listings->macros->do_macros( $start_year );

		if ( false !== strpos( $start_year, 'year' ) && false !== strtotime( $start_year ) ) {
			$start_year = ( int ) wp_date( 'Y', strtotime( $start_year ) );
		}

		if ( ! is_numeric( $start_year ) ) {
			$start_year = 1970;
		}
	}

	$end_year = ! empty( $settings['end_year_select'] ) ? $settings['end_year_select'] : 2038;
	$end_year = jet_engine()->listings->macros->do_macros( $end_year );

	if ( false !== strpos( $end_year, 'year' ) && false !== strtotime( $end_year ) ) {
		$end_year = ( int ) wp_date( 'Y', strtotime( $end_year ) );
	}

	if ( ! is_numeric( $end_year ) ) {
		$end_year = 2038;
	}

	if ( $end_year < $start_year ) {
		list( $start_year, $end_year ) = array( $end_year, $start_year );
	}
}

$allowed_layouts = array(
	'layout-1',
	'layout-2',
	'layout-3',
	'layout-4',
);

$caption_layout = ! empty( $settings['caption_layout'] ) && in_array( $settings['caption_layout'], $allowed_layouts ) ? $settings['caption_layout'] : 'layout-1';

?>
<caption class="jet-calendar-caption">
	<div class="jet-calendar-caption__wrap wrap-<?php echo esc_attr( $caption_layout ); ?>">
		<?php if ( $allow_select ): ?>
			<div class="jet-calendar-caption__name jet-calendar-caption__dates">
				<div class="jet-calendar-caption__select-wrapper">
					<select class="jet-calendar-caption__date-select select-month">
						<?php echo $this->get_month_options(); ?>
					</select>
					<div class="jet-calendar-caption__date-select-label select-month"><?php
						echo date_i18n( 'F', $current_month );
					?></div>
				</div>
				<div class="jet-calendar-caption__select-wrapper">
					<select class="jet-calendar-caption__date-select select-year">
						<?php echo $this->get_year_options( $start_year, $end_year ); ?>
					</select>
					<div class="jet-calendar-caption__date-select-label select-year"><?php
						echo date_i18n( 'Y', $current_month );
					?></div>
				</div>
			</div>
		<?php else: ?>
			<div class="jet-calendar-caption__name"><?php echo date_i18n( 'F Y', $current_month ); ?></div>
		<?php endif ?>
		<div class="jet-calendar-nav__link nav-link-prev" data-month="<?php echo $human_read_prev; ?>">
			<svg viewBox="0 0 90 179" xmlns="http://www.w3.org/2000/svg"><path transform="scale(0.1,-0.1) translate(0,-1536)" d="M627 992q0 -13 -10 -23l-393 -393l393 -393q10 -10 10 -23t-10 -23l-50 -50q-10 -10 -23 -10t-23 10l-466 466q-10 10 -10 23t10 23l466 466q10 10 23 10t23 -10l50 -50q10 -10 10 -23z" /></svg>
		</div>
		<div class="jet-calendar-nav__link nav-link-next" data-month="<?php echo $human_read_next; ?>">
			<svg viewBox="0 0 90 179" xmlns="http://www.w3.org/2000/svg"><path transform="scale(0.1,-0.1) translate(0,-1536)" d="M627 992q0 -13 -10 -23l-393 -393l393 -393q10 -10 10 -23t-10 -23l-50 -50q-10 -10 -23 -10t-23 10l-466 466q-10 10 -10 23t10 23l466 466q10 10 23 10t23 -10l50 -50q10 -10 10 -23z" /></svg>
		</div>
	</div>
</caption>
<thead class="jet-calendar-header">
	<tr class="jet-calendar-header__week"><?php

		for ( $wdcount = 0; $wdcount <= 6; $wdcount++ ) {
			$myweek[] = $wp_locale->get_weekday( ( $wdcount + $week_begins ) % 7 );
		}

		foreach ( $myweek as $wd ) {

			switch ( $days_format ) {
				case 'short':
					$day_name = $wp_locale->get_weekday_abbrev( $wd );
					break;

				case 'initial':
					$day_name = $wp_locale->get_weekday_initial( $wd );
					break;

				default:
					$day_name = $wd;
					break;
			}

			printf( '<th class="jet-calendar-header__week-day">%s</th>', $day_name );
		}

	?></tr>
</thead>