<?php
namespace DeWittePrins\CoreFunctionality\Environment\Plugins;

use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\DTO\ToolbarShortcut;
use DeWittePrins\CoreFunctionality\Environment\DTO\ProofRequirement;
use Override;

use function __;
use function admin_url;

class WpForms extends AbstractPlugin {

	#[Override]
	public function get_id(): string {
		return 'wp-forms';
	}

	#[Override]
	protected function get_proof_requirements(): ProofRequirement {
		return new ProofRequirement( classes: array( 'WPForms\\WPForms' ) );
	}

	#[Override]
	protected function get_toolbar_nodes(): array {
		return array(
			new ToolbarShortcut(
				id:      'cf-wpforms',
				title:   __( 'Forms', 'dwp-cf' ),
				href:    admin_url( 'admin.php?page=wpforms-overview' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'WPForms', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
		);
	}

	#[Override]
	protected function get_menu_nodes(): array { return array(); }
}
