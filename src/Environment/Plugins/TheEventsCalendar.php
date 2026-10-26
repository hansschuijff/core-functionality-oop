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
use function sprintf;

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
		return 'the-events-calendar';
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
			classes:   'Tribe__Events__Main',
			functions: $this->get_defensive_template_tags()
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
	public function get_toolbar_nodes(): array {
		return array(
			new ToolbarShortcut(
				id:      'cf-admin-events-menu',
				title:   __( 'Events', 'core-functionality' ),
				href:    admin_url( 'edit.php?post_type=tribe_events' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Events', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-events-edit-events',
				title:   __( 'Edit', 'core-functionality' ),
				href:    admin_url( 'edit.php?post_type=tribe_events' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Events', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-events-organizers',
				title:   __( 'Organizers', 'core-functionality' ),
				href:    admin_url( 'edit.php?post_type=tribe_organizer' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Event Organizers', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-events-venues',
				title:   __( 'Venues', 'core-functionality' ),
				href:    admin_url( 'edit.php?post_type=tribe_venue' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Event Venues', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-events-categories',
				title:   __( 'Categories', 'core-functionality' ),
				href:    admin_url( 'edit-tags.php?taxonomy=tribe_events_cat&post_type=tribe_events' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Event Categories', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-admin-events-settings',
				title:   __( 'Settings', 'core-functionality' ),
				href:    admin_url( 'edit.php?post_type=tribe_events&page=tec-events-settings' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Events Calendar Settings', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			// voorkant.
			new ToolbarShortcut(
				id:      'cf-view-events',
				title:   __( 'Events', 'core-functionality' ),
				href:    home_url( '/evenementen/' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'The Events Calendar', 'core-functionality' ),
				orientation: Orientation::FRONTEND,
			),
			new ToolbarShortcut(
				id:      'cf-view-events-cat-family-constellations',
				title:   __( 'Family Constellations', 'core-functionality' ),
				href:    home_url( '/agenda/categorie/familieopstellingen/' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Family Constellations', 'core-functionality' ),
				orientation: Orientation::FRONTEND,
			),
			// Pages of The Events Calendar, then its settings tabs (main tab before its sub-tabs).
			new ToolbarShortcut(
				id:          'cf-admin-events-add-new',
				title:       __( 'Add Event', 'core-functionality' ),
				href:        admin_url( 'post-new.php?post_type=tribe_events' ),
				/* translators: %s: title of the page or settings tab. */
				label:       __( 'Link to', 'core-functionality' ) . ' ' . sprintf( __( 'The Events Calendar: %s', 'core-functionality' ), __( 'Add Event', 'core-functionality' ) ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-admin-events-tags',
				title:       __( 'Event Tags', 'core-functionality' ),
				href:        admin_url( 'edit-tags.php?taxonomy=post_tag&post_type=tribe_events' ),
				/* translators: %s: title of the page or settings tab. */
				label:       __( 'Link to', 'core-functionality' ) . ' ' . sprintf( __( 'The Events Calendar: %s', 'core-functionality' ), __( 'Event Tags', 'core-functionality' ) ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-admin-events-import',
				title:       __( 'Import', 'core-functionality' ),
				href:        admin_url( 'edit.php?post_type=tribe_events&page=aggregator' ),
				/* translators: %s: title of the page or settings tab. */
				label:       __( 'Link to', 'core-functionality' ) . ' ' . sprintf( __( 'The Events Calendar: %s', 'core-functionality' ), __( 'Import', 'core-functionality' ) ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-admin-events-calendar-embeds',
				title:       __( 'Calendar Embeds', 'core-functionality' ),
				href:        admin_url( 'edit.php?post_type=tec_calendar_embed' ),
				/* translators: %s: title of the page or settings tab. */
				label:       __( 'Link to', 'core-functionality' ) . ' ' . sprintf( __( 'The Events Calendar: %s', 'core-functionality' ), __( 'Calendar Embeds', 'core-functionality' ) ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-admin-events-help',
				title:       __( 'Help', 'core-functionality' ),
				href:        admin_url( 'edit.php?post_type=tribe_events&page=tec-events-help-hub' ),
				/* translators: %s: title of the page or settings tab. */
				label:       __( 'Link to', 'core-functionality' ) . ' ' . sprintf( __( 'The Events Calendar: %s', 'core-functionality' ), __( 'Help', 'core-functionality' ) ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-admin-events-troubleshooting',
				title:       __( 'Troubleshooting', 'core-functionality' ),
				href:        admin_url( 'edit.php?post_type=tribe_events&page=tec-troubleshooting' ),
				/* translators: %s: title of the page or settings tab. */
				label:       __( 'Link to', 'core-functionality' ) . ' ' . sprintf( __( 'The Events Calendar: %s', 'core-functionality' ), __( 'Troubleshooting', 'core-functionality' ) ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-admin-events-add-ons',
				title:       __( 'Event Add-Ons', 'core-functionality' ),
				href:        admin_url( 'edit.php?post_type=tribe_events&page=tribe-app-shop' ),
				/* translators: %s: title of the page or settings tab. */
				label:       __( 'Link to', 'core-functionality' ) . ' ' . sprintf( __( 'The Events Calendar: %s', 'core-functionality' ), __( 'Event Add-Ons', 'core-functionality' ) ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-admin-events-setup-guide',
				title:       __( 'Setup Guide', 'core-functionality' ),
				href:        admin_url( 'edit.php?post_type=tribe_events&page=first-time-setup' ),
				/* translators: %s: title of the page or settings tab. */
				label:       __( 'Link to', 'core-functionality' ) . ' ' . sprintf( __( 'The Events Calendar: %s', 'core-functionality' ), __( 'Setup Guide', 'core-functionality' ) ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-admin-events-settings-general',
				title:       __( 'General', 'core-functionality' ),
				href:        admin_url( 'edit.php?page=tec-events-settings&tab=general&post_type=tribe_events' ),
				/* translators: %s: title of the page or settings tab. */
				label:       __( 'Link to', 'core-functionality' ) . ' ' . sprintf( __( 'The Events Calendar: %s', 'core-functionality' ), __( 'General', 'core-functionality' ) ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-admin-events-settings-viewing',
				title:       __( 'Viewing', 'core-functionality' ),
				href:        admin_url( 'edit.php?page=tec-events-settings&tab=general-viewing-tab&post_type=tribe_events' ),
				/* translators: %s: title of the page or settings tab. */
				label:       __( 'Link to', 'core-functionality' ) . ' ' . sprintf( __( 'The Events Calendar: %s', 'core-functionality' ), __( 'Viewing', 'core-functionality' ) ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-admin-events-settings-editing',
				title:       __( 'Editing', 'core-functionality' ),
				href:        admin_url( 'edit.php?page=tec-events-settings&tab=general-editing-tab&post_type=tribe_events' ),
				/* translators: %s: title of the page or settings tab. */
				label:       __( 'Link to', 'core-functionality' ) . ' ' . sprintf( __( 'The Events Calendar: %s', 'core-functionality' ), __( 'Editing', 'core-functionality' ) ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-admin-events-settings-maintenance',
				title:       __( 'Maintenance', 'core-functionality' ),
				href:        admin_url( 'edit.php?page=tec-events-settings&tab=general-maintenance-tab&post_type=tribe_events' ),
				/* translators: %s: title of the page or settings tab. */
				label:       __( 'Link to', 'core-functionality' ) . ' ' . sprintf( __( 'The Events Calendar: %s', 'core-functionality' ), __( 'Maintenance', 'core-functionality' ) ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-admin-events-settings-debugging',
				title:       __( 'Debugging', 'core-functionality' ),
				href:        admin_url( 'edit.php?page=tec-events-settings&tab=general-debugging-tab&post_type=tribe_events' ),
				/* translators: %s: title of the page or settings tab. */
				label:       __( 'Link to', 'core-functionality' ) . ' ' . sprintf( __( 'The Events Calendar: %s', 'core-functionality' ), __( 'Debugging', 'core-functionality' ) ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-admin-events-settings-display',
				title:       __( 'Display', 'core-functionality' ),
				href:        admin_url( 'edit.php?page=tec-events-settings&tab=display&post_type=tribe_events' ),
				/* translators: %s: title of the page or settings tab. */
				label:       __( 'Link to', 'core-functionality' ) . ' ' . sprintf( __( 'The Events Calendar: %s', 'core-functionality' ), __( 'Display', 'core-functionality' ) ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-admin-events-settings-calendar',
				title:       __( 'Calendar', 'core-functionality' ),
				href:        admin_url( 'edit.php?page=tec-events-settings&tab=display-calendar-tab&post_type=tribe_events' ),
				/* translators: %s: title of the page or settings tab. */
				label:       __( 'Link to', 'core-functionality' ) . ' ' . sprintf( __( 'The Events Calendar: %s', 'core-functionality' ), __( 'Calendar', 'core-functionality' ) ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-admin-events-settings-date-time',
				title:       __( 'Date & Time', 'core-functionality' ),
				href:        admin_url( 'edit.php?page=tec-events-settings&tab=display-date-time-tab&post_type=tribe_events' ),
				/* translators: %s: title of the page or settings tab. */
				label:       __( 'Link to', 'core-functionality' ) . ' ' . sprintf( __( 'The Events Calendar: %s', 'core-functionality' ), __( 'Date & Time', 'core-functionality' ) ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-admin-events-settings-currency',
				title:       __( 'Currency', 'core-functionality' ),
				href:        admin_url( 'edit.php?page=tec-events-settings&tab=display-currency-tab&post_type=tribe_events' ),
				/* translators: %s: title of the page or settings tab. */
				label:       __( 'Link to', 'core-functionality' ) . ' ' . sprintf( __( 'The Events Calendar: %s', 'core-functionality' ), __( 'Currency', 'core-functionality' ) ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-admin-events-settings-maps',
				title:       __( 'Maps', 'core-functionality' ),
				href:        admin_url( 'edit.php?page=tec-events-settings&tab=display-maps-tab&post_type=tribe_events' ),
				/* translators: %s: title of the page or settings tab. */
				label:       __( 'Link to', 'core-functionality' ) . ' ' . sprintf( __( 'The Events Calendar: %s', 'core-functionality' ), __( 'Maps', 'core-functionality' ) ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-admin-events-settings-additional-content',
				title:       __( 'Additional Content', 'core-functionality' ),
				href:        admin_url( 'edit.php?page=tec-events-settings&tab=display-additional-content-tab&post_type=tribe_events' ),
				/* translators: %s: title of the page or settings tab. */
				label:       __( 'Link to', 'core-functionality' ) . ' ' . sprintf( __( 'The Events Calendar: %s', 'core-functionality' ), __( 'Additional Content', 'core-functionality' ) ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-admin-events-settings-category-colors',
				title:       __( 'Category Colors', 'core-functionality' ),
				href:        admin_url( 'edit.php?page=tec-events-settings&tab=display-category-colors-settings&post_type=tribe_events' ),
				/* translators: %s: title of the page or settings tab. */
				label:       __( 'Link to', 'core-functionality' ) . ' ' . sprintf( __( 'The Events Calendar: %s', 'core-functionality' ), __( 'Category Colors', 'core-functionality' ) ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-admin-events-settings-seo',
				title:       __( 'SEO & URLs', 'core-functionality' ),
				href:        admin_url( 'edit.php?page=tec-events-settings&tab=display-seo-settings&post_type=tribe_events' ),
				/* translators: %s: title of the page or settings tab. */
				label:       __( 'Link to', 'core-functionality' ) . ' ' . sprintf( __( 'The Events Calendar: %s', 'core-functionality' ), __( 'SEO & URLs', 'core-functionality' ) ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-admin-events-settings-licenses',
				title:       __( 'Licenses', 'core-functionality' ),
				href:        admin_url( 'edit.php?page=tec-events-settings&tab=licenses&post_type=tribe_events' ),
				/* translators: %s: title of the page or settings tab. */
				label:       __( 'Link to', 'core-functionality' ) . ' ' . sprintf( __( 'The Events Calendar: %s', 'core-functionality' ), __( 'Licenses', 'core-functionality' ) ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-admin-events-settings-integrations',
				title:       __( 'Integrations', 'core-functionality' ),
				href:        admin_url( 'edit.php?page=tec-events-settings&tab=addons&post_type=tribe_events' ),
				/* translators: %s: title of the page or settings tab. */
				label:       __( 'Link to', 'core-functionality' ) . ' ' . sprintf( __( 'The Events Calendar: %s', 'core-functionality' ), __( 'Integrations', 'core-functionality' ) ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-admin-events-settings-google-maps',
				title:       'Google Maps',
				href:        admin_url( 'edit.php?page=tec-events-settings&tab=google-maps&post_type=tribe_events' ),
				/* translators: %s: title of the page or settings tab. */
				label:       __( 'Link to', 'core-functionality' ) . ' ' . sprintf( __( 'The Events Calendar: %s', 'core-functionality' ), 'Google Maps' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-admin-events-settings-import',
				title:       __( 'Import', 'core-functionality' ),
				href:        admin_url( 'edit.php?page=tec-events-settings&tab=integrations-import-tab&post_type=tribe_events' ),
				/* translators: %s: title of the page or settings tab. */
				label:       __( 'Link to', 'core-functionality' ) . ' ' . sprintf( __( 'The Events Calendar: %s', 'core-functionality' ), __( 'Import', 'core-functionality' ) ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-admin-events-settings-imports',
				title:       __( 'Imports', 'core-functionality' ),
				href:        admin_url( 'edit.php?page=tec-events-settings&tab=imports&post_type=tribe_events' ),
				/* translators: %s: title of the page or settings tab. */
				label:       __( 'Link to', 'core-functionality' ) . ' ' . sprintf( __( 'The Events Calendar: %s', 'core-functionality' ), __( 'Imports', 'core-functionality' ) ),
				orientation: Orientation::ADMIN,
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
