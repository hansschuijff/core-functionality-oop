<?php
/**
 * View: List Single Event Cost
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/tickets/v2/list/event/cost.php
 *
 * Original:
 * plugins/event-tickets/src/views/v2/list/event/cost.php
 *
 * Reason for customizing:
 * Show available remote tickets too with buy button.
 *
 * See more documentation about our views templating system.
 *
 * @link https://evnt.is/1amp Help article for RSVP & Ticket template files.
 *
 * @since   4.10.9
 * @version 4.12.0
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */

// start tvu added

use function \DeWittePrins\EventRemoteTickets\has_remote_available_tickets;
use function \DeWittePrins\EventRemoteTickets\get_remote_tickets_stock;

$check_remote_tickets = function_exists( '\DeWittePrins\EventRemoteTickets\has_remote_available_tickets' );

// end tvu added

if ( empty( $event->cost ) ) {
	return;
}

// begin tvu added
if ( tribe_is_past_event( $event ) ) {
	// no price, stock notice or buy button for past events.
	return;
}
// end tvu added
?>
<div class="tribe-events-c-small-cta tribe-common-b3 tribe-events-calendar-list__event-cost">
	<?php if ( $event->tickets->exist() && $event->tickets->in_date_range() && ! $event->tickets->sold_out() ) : ?>
		<a
			href="<?php echo esc_url( $event->tickets->link->anchor ); ?>"
			class="tribe-events-c-small-cta__link tribe-common-cta tribe-common-cta--thin-alt"
		>
			<?php echo esc_html( $event->tickets->link->label ); ?>
		</a>
	<!-- begin tvu added -->
	<!-- check for remote_tickets_stock and show buy link if it does -->
	<?php elseif ( $check_remote_tickets && has_remote_available_tickets( $event ) ) : ?>
		<a
			href="<?php echo ( esc_url( isset( $event->tickets->link->label ) ? $event->tickets->link->anchor : get_permalink( $event->ID ) ) ? get_permalink( $event->ID ) . '#tribe-tickets__tickets-form' : '#' ); ?>"
			class="tribe-events-c-small-cta__link tribe-common-cta tribe-common-cta--thin-alt"
		>
			<?php echo esc_html( isset( $event->tickets->link->label ) ? $event->tickets->link->label : 'Koop tickets' ); ?>
		</a>
	<!-- end tvu added -->
	<?php endif; ?>
	<?php if ( $event->tickets->sold_out() ) : ?>
		<span class="tribe-events-c-small-cta__sold-out tribe-common-b3--bold">
			<?php echo esc_html( $event->tickets->stock->sold_out ); ?>
		</span>
	<?php endif; ?>
	<span class="tribe-events-c-small-cta__price">
		<?php echo esc_html( $event->cost ); ?>
	</span>
	<?php if ( ! empty( $event->tickets->stock->available ) && $event->tickets->in_date_range() ) : ?>
		<span class="tribe-events-c-small-cta__stock">
			<?php echo esc_html( $event->tickets->stock->available ); ?>
		</span>
	<!-- start tvu added -->
	<?php
	elseif ( $check_remote_tickets && has_remote_available_tickets( $event ) ) :
		$remote_tickets_stock = get_remote_tickets_stock( $event );
		if ( false !== $remote_tickets_stock
		&& (
			(int) $remote_tickets_stock > 0
			|| ( is_string( $remote_tickets_stock ) && __( 'unlimited', 'event-remote-tickets-dwp' ) === $remote_tickets_stock )
		) ) :
			?>
			<span class="tribe-events-c-small-cta__stock">
				<?php echo esc_html( (string) $remote_tickets_stock . ' tickets beschikbaar' ); ?>
			</span>
		<?php endif; ?>
	<?php elseif ( ! $event->tickets->exist() ) : ?>
		<span class="tribe-events-c-small-cta__stock">
			<?php if ( ! empty( $event->costDescription ) ) {
				echo esc_html( $event->costDescription );
			} else {
				echo esc_html( 'per consult' );
			}
				?>
			</span>
		<!-- end tvu added -->
	<?php endif; ?>
</div>
