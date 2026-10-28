<?php
/**
 * Single Event Header Template Part
 *
 * Original template:
 * This template doesn't exist in The Events Calendar plugin.
 *
 * The base markup and code is copied from the v1 single event template.
 *
 * The template functionality of TEC allows for overriding a template, even if the original doesn't exist.
 *
 * Reason for customization:
 * Enable navigation to the next and previous event in a header above the content.
 *
 * @custom-template
 * @version 1.0.0
 */
?>

<?php
$events_label_singular = tribe_get_event_label_singular();
?>
<!-- Event header -->
<div id="tribe-events-header" <?php tribe_events_the_header_attributes(); ?>>
	<!-- Navigation -->
	<nav class="tribe-events-nav-pagination" aria-label="<?php printf( esc_html__( '%s Navigation', 'the-events-calendar' ), $events_label_singular ); ?>">
		<ul class="tribe-events-sub-nav">
			<li class="tribe-events-nav-previous"><?php tribe_the_prev_event_link( '<span>&laquo;</span> %title%' ); ?></li>
			<li class="tribe-events-nav-next"><?php tribe_the_next_event_link( '%title% <span>&raquo;</span>' ); ?></li>
		</ul>
		<!-- .tribe-events-sub-nav -->
	</nav>
</div>
<!-- #tribe-events-header -->
