<?php
namespace DeWittePrins\CoreFunctionality\Environment\Plugins;

use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\DTO\ToolbarShortcut;
use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\Environment\DTO\ProofRequirement;
use Override;

use function __;
use function admin_url;

class GitRemoteUpdater extends AbstractPlugin {

	#[Override]
	public function get_id(): string {
		return 'git-updater';
	}

	#[Override]
	protected function get_proof_requirements(): ProofRequirement {
		return new ProofRequirement( classes: array( 'Fragen\Git_Remote_Updater\Bootstrap' ) );
	}

	#[Override]
	protected function get_toolbar_nodes(): array {
		return array(
			new ToolbarShortcut(
				id:      'cf-git-remote-updater',
				title:   __( 'Remote Updater', 'dwp-cf' ),
				href:    admin_url( 'admin.php?page=git-remote-updater' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Git Remote Updater', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			)
		);
	}

	#[Override]
	protected function get_menu_nodes(): array { return array(); }
}
