<?php
/**
 * Wanneer de Verkeerstoren een feature lanceert, kunnen we de hoofdmodule
 * (en al zijn handige pad-helpers) via de constructor direct injecteren in de feature!
 * Kijk hoe waanzinnig elegant je code daardoor wordt:
 */
namespace DeWittePrins\Modules\TheEventsCalendar\Features;

use DeWittePrins\Core\Contracts\FeatureInterface;
use DeWittePrins\Modules\TheEventsCalendar\Module;

class LoadTicketsCustomScript implements FeatureInterface {
	private Module $module;

	// De feature krijgt bij de start automatisch het brein van de hoofdmodule mee!
	public function __construct( Module $module ) {
		$this->module = $module;
	}

	public function get_id(): string { return 'tec_load_custom_script'; }
	public function get_preflight_checks(): array { return array(); }

	public function launch(): void {
		add_action( 'wp_enqueue_scripts', function() {
			// JE GEBRUIKT JE VERTOUWDE HELPER DIRECT VIA DE MODULE! 🎯
			// Geen hardcoded paden meer, de module weet exact waar de assets-url leeft.
			wp_enqueue_script(
				'dwp-custom-frontend',
				$this->module->get_module_data( 'assets-url' ) . 'js/frontend.js',
				array( 'jquery' ),
				'1.0.0',
				true
			);
		});
	}
}
