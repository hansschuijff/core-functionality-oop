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
		return new ProofRequirement( constants: 'FLUENTMAIL_PLUGIN_FILE' );
	}

	#[Override]
	protected function get_toolbar_nodes(): array {
		return array(
			new ToolbarShortcut(
				id:      'cf-fluentsmtp',
				title:   __( 'Emails Send', 'core-functionality' ),
				href:    admin_url( 'options-general.php?page=fluent-mail#/logs?per_page=10&page=1&status=&search=' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'FluentSMTP', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
		);
	}

	#[Override]
	protected function get_menu_nodes(): array {
		return array(); }
}
