<?php
namespace DeWittePrins\CoreFunctionality\Environment\Plugins;

use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\DTO\ToolbarShortcut;
use DeWittePrins\CoreFunctionality\Environment\DTO\ProofRequirement;
use Override;

use function __;
use function admin_url;

class GitRemoteUpdater extends AbstractPlugin {

	#[Override]
	public function get_id(): string {
		return 'git_remote_updater';
	}

	#[Override]
	protected function get_proof_requirements(): ProofRequirement {
		// Git Remote Updater only loads its own classes on init, and only when Git Updater runs.
		return new ProofRequirement( classes: 'Fragen\\Git_Updater\\Bootstrap' );
	}

	#[Override]
	protected function get_toolbar_nodes(): array {
		return array(
			new ToolbarShortcut(
				id:      'cf-git-remote-updater',
				title:   __( 'Remote Updater', 'core-functionality' ),
				href:    admin_url( 'admin.php?page=git-remote-updater' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Git Remote Updater', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			)
		);
	}

	#[Override]
	protected function get_menu_nodes(): array { return array(); }
}
