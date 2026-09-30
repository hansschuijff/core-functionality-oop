<?php
namespace DeWittePrins\CoreFunctionality\Environment\Plugins;

use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\DTO\ToolbarShortcut;
use DeWittePrins\CoreFunctionality\Environment\DTO\ProofRequirement;
use Override;

use function __;
use function admin_url;

class Nobuna extends AbstractPlugin {

	#[Override]
	public function get_id(): string {
		return 'nobuna';
	}

	#[Override]
	protected function get_proof_requirements(): ProofRequirement {
		return new ProofRequirement( classes: 'Nobuna_Plugins' );
	}

	#[Override]
	protected function get_toolbar_nodes(): array {
		return array(
			new ToolbarShortcut(
				id:          'cf-nobuna-plugins',
				title:       __( 'Nobuna', 'dwp-cf' ),
				href:        admin_url( 'admin.php?page=nobuna-plugins' ),
				label:       __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Nobuna Plugins', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
		);
	}

	#[Override]
	protected function get_menu_nodes(): array { return array(); }
}
