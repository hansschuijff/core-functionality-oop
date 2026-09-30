<?php
/**
 * The Events Calendar Verification Guard.
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
 * Class TheEventsCalendar
 *
 * Validates the runtime availability of the core The Events Calendar environment
 * bypassing empty class hulls by invoking functional endpoint tests.
 *
 * @since 1.0.0
 */
class TheEventsCalendar extends AbstractPlugin {

	/**
	 * Unique identifier token for this specific ecosystem component.
	 *
	 * @since 1.0.0
	 * @return string The blueprint identification token.
	 */
	#[Override]
	public function get_id(): string {
		return 'the_events_calendar';
	}

	/**
	 * Declares the extensive defensive proof criteria required for verification.
	 *
	 * Fills the declarative requirements. Single values do not need array wrappers.
	 *
	 * @since 1.0.0
	 * @return ProofRequirement The highly defensive proof criteria object.
	 */
	#[Override]
	protected function get_proof_requirements(): ProofRequirement {
		return new ProofRequirement(
			classes:     'Tribe__Events__Main',
			functions:   $this->get_defensive_template_tags(),
			not_actions: 'tribe_extensions_failed_requirements' // Negative action hook firewall!
		);
	}

	/**
	 * Compiles and returns the available pool of toolbar shortcuts this plugin can offer.
	 *
	 * Replaces get_raw_node_data by converting the link criteria into a type-safe ToolbarShortcut DTO block.
	 *
	 * @since 1.0.0
	 * @return ToolbarShortcut[] Collection of plugin toolbar shortcut DTOs.
	 */
	#[Override]
	protected function get_toolbar_nodes(): array {
		return array(
			new ToolbarShortcut(
				id:      'cf-admin-events-menu',
				title:   __( 'Events', 'dwp-cf' ),
				href:    admin_url( 'edit.php?post_type=tribe_events' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Events', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-events-edit-events',
				title:   __( 'Edit', 'dwp-cf' ),
				href:    admin_url( 'edit.php?post_type=tribe_events' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Events', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-events-organizers',
				title:   __( 'Organizers', 'dwp-cf' ),
				href:    admin_url( 'edit.php?post_type=tribe_organizer' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Event Organizers', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-events-venues',
				title:   __( 'Venues', 'dwp-cf' ),
				href:    admin_url( 'edit.php?post_type=tribe_venue' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Event Venues', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-events-categories',
				title:   __( 'Categories', 'dwp-cf' ),
				href:    admin_url( 'edit-tags.php?taxonomy=tribe_events_cat&post_type=tribe_events' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Event Categories', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-events-settings',
				title:   __( 'Settings', 'dwp-cf' ),
				href:    admin_url( 'edit.php?post_type=tribe_events&page=tec-events-settings' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Events Calendar Settings', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
			// voorkant
			new ToolbarShortcut(
				id:      'cf-view-events',
				title:   __( 'Events', 'dwp-cf' ),
				href:    home_url( '/evenementen/' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'The Events Calendar', 'dwp-cf' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-view-events-cat-family-constellations',
				title:   __( 'Family Constellations', 'dwp-cf' ),
				href:    home_url( '/agenda/categorie/familieopstellingen/' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Family Constellations', 'dwp-cf' ),
				orientation: Orientation::FRONTEND,
			),
		);
	}

	/**
	 * Compiles and returns the available pool of admin menu blocks this plugin can offer.
	 *
	 * Returns an empty array if no custom core functionality submenus are needed.
	 *
	 * @since 1.0.0
	 * @return AdminMenuBlock[] Collection of plugin sidebar menu blocks.
	 */
	#[Override]
	protected function get_menu_nodes(): array {
		return array();
	}

	/**
	 * Retrieves the exact defensive collection of historical and modern template tags to test.
	 *
	 * @return string[] Names of crucial functions that must be loaded by PHP to verify readiness.
	 */
	private function get_defensive_template_tags(): array {
		return array(
			'tribe',
			'tribe_is_event',
			'tribe_is_venue',
			'tribe_is_organizer',
			'tribe_is_event_query',
			'tribe_is_event_category',
			'tribe_is_past_event',
			'tribe_is_upcoming',
			'tribe_is_showing_all',
			'tribe_is_by_date',
			'tribe_is_in_main_loop',
			'tribe_is_list_view',
			'tribe_is_past',
			'tribe_is_month',
			'tribe_is_day',
			'tec_is_full_site_editor',
			'tec_is_file_from_plugins',
			'tec_is_view',
		);
	}
}
