<?php
/**
 * Event Tickets Core Extension Verification Guard.
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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class EventTickets
 *
 * Inspects execution states checking if the core Event Tickets plugin
 * is fully operational utilizing the translation mapping pipeline.
 *
 * @since 1.0.0
 */
class EventTickets extends AbstractPlugin {

	/**
	 * Unique identifier token for this specific ecosystem component.
	 *
	 * Must return 'event_tickets' to align with the core preflight registry mappings.
	 *
	 * @since 1.0.0
	 * @return string The blueprint identification token.
	 */
	#[Override]
	public function get_id(): string {
		return 'event_tickets';
	}

	/**
	 * Declares the specific technical proof criteria required for Event Tickets core verification.
	 *
	 * Matches the core class string layout and attaches the defensive version conflict firewall.
	 *
	 * @since 1.0.0
	 * @return ProofRequirement The structured proof criteria object.
	 */
	#[Override]
	protected function get_proof_requirements(): ProofRequirement {
		return new ProofRequirement(
			classes:     'Tribe__Tickets__Main', // Corrected to the explicit Core class for the free version!
			not_actions: 'tribe_extensions_failed_requirements' // Global version mismatch monitor guard
		);
	}

	/**
	 * Compiles and returns the available pool of toolbar shortcuts this plugin can offer.
	 *
	 * Transforms your link criteria directly into a type-safe ToolbarShortcut DTO block.
	 *
	 * @since 1.0.0
	 * @return ToolbarShortcut[] Collection of plugin toolbar shortcut DTOs.
	 */
	#[Override]
	protected function get_toolbar_nodes(): array {
		return array(
			new ToolbarShortcut(
				id:      'event-tickets-link',
				title:   __( 'Event Tickets', 'core-functionality-oop' ),
				href:    admin_url( 'plugins.php#event-tickets' )
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Event Tickets Dashboard', 'dwp-cf' ),
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
