<?php
namespace DeWittePrins\CoreFunctionality\Environment\Plugins;

use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\DTO\ToolbarShortcut;
use DeWittePrins\CoreFunctionality\Environment\DTO\ProofRequirement;
use Override;

use function __;
use function admin_url;

class SharedCounts extends AbstractPlugin {

	#[Override]
	public function get_id(): string {
		return 'shared-counts';
	}

	#[Override]
	protected function get_proof_requirements(): ProofRequirement {
		return new ProofRequirement( functions: array( 'shared_counts' ) );
	}

	#[Override]
	protected function get_toolbar_nodes(): array {
		return array(
			new ToolbarShortcut(
				id:      'cf-settings-social-share',
				title:   __( 'Shared Counts', 'dwp-cf' ),
				href:    admin_url( 'options-general.php?page=shared_counts_options' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Shared Counts', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
		);
	}

	#[Override]
	protected function get_menu_nodes(): array { return array(); }
}
