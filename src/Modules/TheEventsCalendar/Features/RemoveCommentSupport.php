<?php
namespace DeWittePrins\Modules\TheEventsCalendar\Features;

use DeWittePrins\Core\Contracts\FeatureInterface;

class RemoveCommentSupport implements FeatureInterface {

	public function get_id(): string {
		// Het unieke ID voor jouw aan/uit vinkje in het settingsscherm!
		return 'tec_remove_comment_support'; // De database/config sleutel
	}

	public function get_html_id(): string {
		// Vervangt de underscores automatisch door streepjes voor je CSS/HTML!
		return str_replace('_', '-', $this->get_id()); // Wordt: 'tec-remove-comment-support'
	}

	public function get_preflight_checks(): array {
		// Deze feature heeft geen extra eisen buiten de hoofdmodule om
		return array();
	}

	public function launch(): void {
		// HIER STAAT JOUW SIMPELE FUNCTIE! 🎯
		// Geen ingewikkelde klasses, gewoon de WordPress hook zoals je hem gewend bent.
		add_action( 'init', function() {
			remove_post_type_support( 'tribe_events', 'comments' );
		});
	}
}
