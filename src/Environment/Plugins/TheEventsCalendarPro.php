<?php
/**
 * The Events Calendar Pro Verification Guard.
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
 * Class TheEventsCalendarPro
 *
 * Validates the runtime availability of the core The Events Calendar Pro environment
 * bypassing empty class hulls by invoking functional endpoint tests.
 *
 * @since 1.0.0
 */
class TheEventsCalendarPro extends AbstractPlugin {

	/**
	 * Unique identifier token for this specific ecosystem component.
	 *
	 * @since 1.0.0
	 * @return string The blueprint identification token.
	 */
	#[Override]
	public function get_id(): string {
		return 'the_events_calendar_pro';
	}

	/**
	 * Declares the extensive defensive proof criteria required for Pro verification.
	 *
	 * Fills the declarative requirements matrix including specialized Pro functions and core classes.
	 *
	 * @since 1.0.0
	 * @return ProofRequirement The highly defensive proof criteria object.
	 */
	#[Override]
	protected function get_proof_requirements(): ProofRequirement {
		return new ProofRequirement(
			classes:     array( 'Tribe__Events__Main', 'Tribe__Events__Pro__Main' ),
			functions:   $this->get_defensive_pro_template_tags(),
			not_actions: 'tribe_extensions_failed_requirements' // Global version conflict monitor guard
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
	protected function get_toolbar_nodes(): array {
		return array(
			new ToolbarShortcut(
				id:      'the_events_calendar_pro_link',
				title:   __( 'Events Calendar Pro', 'dwp-cf' ),
				href:    admin_url( 'edit.php?post_type=tribe_events&page=ticket-settings' ) // Example target to direct to specialized Pro management panel
				label:   __( 'The Events Calendar Pro Settings', 'dwp-cf' ),
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

	/**
	 * Retrieves the exact defensive collection of historical and modern template tags for Pro to test.
	 *
	 * @return string[] Names of crucial functions that must be loaded by PHP to verify readiness.
	 */
	private function get_defensive_pro_template_tags(): array {
		return array(
			'tribe',
			// Defined in: events-calendar-pro/src/functions/template-tags/general.php
			'tribe_is_week',
			'tribe_is_map',
			'tribe_is_photo',
			'tec_is_venue_view',
			'tec_is_organizer_view',
			// Defined in: events-calendar-pro/events-calendar-pro.php and general.php
			'tribe_is_recurring_event',
			// Defined in: events-calendar-pro/src/functions/template-tags/series.php
			'tribe_is_event_series',
		);
	}
}
