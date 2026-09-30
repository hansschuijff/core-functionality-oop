<?php
namespace DeWittePrins\CoreFunctionality\Environment\Plugins;

use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\DTO\ToolbarShortcut;
use DeWittePrins\CoreFunctionality\Environment\DTO\ProofRequirement;
use Override;

use function __;
use function admin_url;

class UpdraftPlus extends AbstractPlugin {

	#[Override]
	public function get_id(): string {
		return 'updraft-plus';
	}

	#[Override]
	protected function get_proof_requirements(): ProofRequirement {
		return new ProofRequirement( classes: array( 'UpdraftPlus' ) );
	}

	#[Override]
	protected function get_toolbar_nodes(): array {
		return array(
			new ToolbarShortcut(
				id:      'cf-updraft-backups-menu',
				title:   __( 'Updraft Backups', 'dwp-cf' ),
				href:    admin_url( 'options-general.php?page=updraftplus' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Updraft Backups', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-updraft-backups',
				title:   __( 'Updraft Backups', 'dwp-cf' ),
				href:    admin_url( 'options-general.php?page=updraftplus' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Updraft Backups', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
		);
	}

	#[Override]
	protected function get_menu_nodes(): array { return array(); }
}
