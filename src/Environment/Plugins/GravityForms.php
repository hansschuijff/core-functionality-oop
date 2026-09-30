<?php
namespace DeWittePrins\CoreFunctionality\Environment\Plugins;

use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\DTO\ToolbarShortcut;
use DeWittePrins\CoreFunctionality\Environment\DTO\ProofRequirement;
use Override;

use function __;
use function admin_url;

class GravityForms extends AbstractPlugin {

	#[Override]
	public function get_id(): string {
		return 'gravityforms';
	}

	#[Override]
	protected function get_proof_requirements(): ProofRequirement {
		return new ProofRequirement( classes: 'GFCommon' );
	}

	#[Override]
	protected function get_toolbar_nodes(): array {
		return array(
			new ToolbarShortcut(
				id:      'cf-gravityforms',
				title:   __( 'Forms', 'dwp-cf' ),
				href:    admin_url( 'admin.php?page=gf_edit_forms' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Gravity Forms', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
		);
	}

	#[Override]
	protected function get_menu_nodes(): array { return array(); }
}
