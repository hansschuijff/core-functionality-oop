<?php
/**
 * Event Tickets Plus Premium Extension Verification Guard.
 *
 * @package    DeWittePrins\CoreFunctionality
 * @subpackage Environment\Plugins
 * @author     Hans Schuijff <@hansschuijff>
 * @license    GPL-2.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace DeWittePrins\CoreFunctionality\Environment\Plugins;

use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\DTO\AdminMenuBlock;
use DeWittePrins\CoreFunctionality\DTO\ToolbarShortcut;
use DeWittePrins\CoreFunctionality\Environment\DTO\ProofRequirement;
use Override;

use function __;
use function admin_url;
use function sprintf;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class EventTicketsPlus
 *
 * Inspects execution states checking if the premium Event Tickets Plus add-on
 * is fully operational utilizing the translation mapping pipeline.
 *
 * @since 1.0.0
 */
class EventTicketsPlus extends AbstractPlugin {

	/**
	 * Unique identifier token for this specific ecosystem component.
	 *
	 * @since 1.0.0
	 * @return string The blueprint identification token.
	 */
	#[Override]
	public function get_id(): string {
		return 'event-tickets-plus';
	}

	/**
	 * Declares the specific technical proof criteria required for Event Tickets Plus verification.
	 *
	 * Defensively tests the explicit Plus core class and handles the negative version conflict check.
	 *
	 * @since 1.0.0
	 * @return ProofRequirement The structured proof criteria object.
	 */
	#[Override]
	protected function get_proof_requirements(): ProofRequirement {
		return new ProofRequirement(
			classes:   array( 'Tribe__Tickets__Main', 'Tribe__Tickets_Plus__Main' ),
			functions: array( 'tribe_tickets_plus_meta_field_is_required', 'tribe_tickets_plus_meta_field_name' )
		);
	}

	/**
	 * Compiles and returns the available pool of toolbar shortcuts this plugin can offer.
	 *
	 * Transforms your link data into a type-safe ToolbarShortcut DTO block.
	 *
	 * @since 1.0.0
	 * @return ToolbarShortcut[] Collection of plugin toolbar shortcut DTOs.
	 */
	#[Override]
	public function get_toolbar_nodes(): array {
		return array(
			new ToolbarShortcut(
				id:      'event-tickets-plus-link',
				title:   __( 'Event Tickets Plus', 'core-functionality' ),
				href:    admin_url( 'admin.php?page=tec-tickets-settings' ),
				label:   __( 'Event Tickets Settings', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			// Pages Event Tickets Plus adds to the Tickets menu.
			new ToolbarShortcut(
				id:          'cf-tickets-plus-purchase-rules',
				title:       __( 'Purchase Rules', 'core-functionality' ),
				href:        admin_url( 'admin.php?page=tec-tickets-plus-admin-purchase-rules' ),
				/* translators: %s: title of the page or settings tab. */
				label:       __( 'Link to', 'core-functionality' ) . ' ' . sprintf( __( 'Event Tickets Plus: %s', 'core-functionality' ), __( 'Purchase Rules', 'core-functionality' ) ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-tickets-plus-waitlist',
				title:       __( 'Waitlist Subscribers', 'core-functionality' ),
				href:        admin_url( 'admin.php?page=tec-tickets-plus-admin-waitlist-subscribers' ),
				/* translators: %s: title of the page or settings tab. */
				label:       __( 'Link to', 'core-functionality' ) . ' ' . sprintf( __( 'Event Tickets Plus: %s', 'core-functionality' ), __( 'Waitlist Subscribers', 'core-functionality' ) ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-tickets-plus-fieldsets',
				title:       __( 'Ticket Fieldsets', 'core-functionality' ),
				href:        admin_url( 'edit.php?post_type=ticket-meta-fieldset' ),
				/* translators: %s: title of the page or settings tab. */
				label:       __( 'Link to', 'core-functionality' ) . ' ' . sprintf( __( 'Event Tickets Plus: %s', 'core-functionality' ), __( 'Ticket Fieldsets', 'core-functionality' ) ),
				orientation: Orientation::ADMIN,
			),
		);
	}

	/**
	 * Compiles and returns the available pool of admin menu blocks this plugin can offer.
	 *
	 * @since 1.0.0
	 * @return AdminMenuBlock[] Collection of plugin sidebar menu blocks.
	 */
	#[Override]
	protected function get_menu_nodes(): array {
		return array();
	}
}
