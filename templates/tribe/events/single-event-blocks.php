<?php
/**
 * Single Event Template
 *
 * Original template:
 * [the-events-calendar]\src\views\single-event-blocks.php
 *
 * Reason for customization:
 * Add a header block to the single event page that contains nav to next and prev event.
 * When the title needs to be suppressed, just comment out (or remove) the call to that template.
 *
 * A single event complete template, divided in smaller template parts.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/single-event-blocks.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 4.7
 */

$event_id = $this->get( 'post_id' );

$is_recurring = '';

if ( ! empty( $event_id ) && function_exists( 'tribe_is_recurring_event' ) ) {
	$is_recurring = tribe_is_recurring_event( $event_id );
}
?>

<div id="tribe-events-content" class="tribe-events-single tribe-blocks-editor">
	<?php $this->template( 'single-event/back-link' ); ?>
	<?php $this->template( 'single-event/header' ); ?>
	<?php $this->template( 'single-event/notices' ); ?>
	<!-- Remove the next line to suppress the title when the theme has a header block with the title. -->
	<?php $this->template( 'single-event/title' ); ?>
	<?php if ( $is_recurring ) { ?>
		<?php $this->template( 'single-event/recurring-description' ); ?>
	<?php } ?>
	<?php $this->template( 'single-event/content' ); ?>
	<?php $this->template( 'single-event/comments' ); ?>
	<?php $this->template( 'single-event/footer' ); ?>
</div>
