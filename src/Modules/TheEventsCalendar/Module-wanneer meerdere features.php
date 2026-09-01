<?php
/**
 * Voorbeeld van Module met meerdere features om te laden.
 */
namespace DeWittePrins\Modules\TheEventsCalendar;

use DeWittePrins\Core\Contracts\ModuleInterface;

class Module implements ModuleInterface {

	public function get_preflight_checks(): array {
		return array(
			'and' => array( 'the_events_calendar' ) // De basis-eis
		);
	}

	public function get_features(): array {
		// JOUW OVERZICHTELIJKE FLIGHT-LIJST ✈️
		// Je registreert hier simpelweg de klassenamen van de features.
		return array(
			Features\RemoveCommentSupport::class,
			Features\PreventPostHijacking::class,
			Features\WooCommerceConnect::class,
		);
	}

	public function launch(): void {
		// Eventuele algemene basis-instellingen of assets voor deze module
	}
}
