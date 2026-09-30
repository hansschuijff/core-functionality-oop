<?php
namespace DeWittePrins\CoreFunctionality\Environment\Plugins;

use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\DTO\ToolbarShortcut;
use DeWittePrins\CoreFunctionality\Environment\DTO\ProofRequirement;
use Override;

use function __;
use function admin_url;

class FluentSmtp extends AbstractPlugin {

	#[Override]
	public function get_id(): string {
		return 'fluent-smtp';
	}

	#[Override]
	protected function get_proof_requirements(): ProofRequirement {
		return new ProofRequirement( classes: array( 'FluentMail\\App\\App' ) );
	}

	#[Override]
	protected function get_toolbar_nodes(): array {
		return array(
			new ToolbarShortcut(
				id:      'cf-fluentsmtp',
				title:   __( 'Emails Send', 'dwp-cf' ),
				href:    admin_url( 'options-general.php?page=fluent-mail#/logs?per_page=10&page=1&status=&search=' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'FluentSMTP', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
		);
	}

	#[Override]
	protected function get_menu_nodes(): array { return array(); }
}
