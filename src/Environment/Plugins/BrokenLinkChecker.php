<?php
namespace DeWittePrins\CoreFunctionality\Environment\Plugins;

use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\DTO\ToolbarShortcut;
use DeWittePrins\CoreFunctionality\Environment\DTO\ProofRequirement;
use Override;

use function __;
use function admin_url;

class BrokenLinkChecker extends AbstractPlugin {

	#[Override]
	public function get_id(): string {
		return 'broken-link-checker';
	}

	#[Override]
	protected function get_proof_requirements(): ProofRequirement {
		return new ProofRequirement( classes: array( 'ws_blc_BlcCore' ) );
	}

	#[Override]
	protected function get_toolbar_nodes(): array {
		return array(
			new ToolbarShortcut(
				id:      'cf-view-broken-links',
				title:   __( 'Broken Links Checker', 'dwp-cf' ),
				href:    admin_url( 'tools.php?page=view-broken-links' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Broken Links Checker', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
		);
	}

	#[Override]
	protected function get_menu_nodes(): array { return array(); }
}
