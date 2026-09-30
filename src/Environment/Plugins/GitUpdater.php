<?php
namespace DeWittePrins\CoreFunctionality\Environment\Plugins;

use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\DTO\ToolbarShortcut;
use DeWittePrins\CoreFunctionality\Environment\DTO\ProofRequirement;
use Override;

use function __;
use function admin_url;

class GitUpdater extends AbstractPlugin {

	#[Override]
	public function get_id(): string {
		return 'git-updater';
	}

	#[Override]
	protected function get_proof_requirements(): ProofRequirement {
		return new ProofRequirement( classes: array( 'Fragen\Git_Updater\Bootstrap' ) );
	}

	#[Override]
	protected function get_toolbar_nodes(): array {
		return array(
			new ToolbarShortcut(
				id:      'cf-git-updater-menu',
				title:   __( 'Github', 'dwp-cf' ),
				href:    admin_url( 'admin.php?page=git-remote-updater' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Git Remote Updater', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-git-updater-settings',
				title:   __( 'Settings', 'dwp-cf' ),
				href:    admin_url( 'options-general.php?page=git-updater&tab=git_updater_settings&subtab=github' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Git Updater: Github settings', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-git-updater-plugin-install',
				title:   __( 'Install plugin', 'dwp-cf' ),
				href:    admin_url( 'options-general.php?page=git-updater&tab=git_updater_install_plugin' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Git Updater: Install Plugin', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-git-updater-theme-install',
				title:   __( 'Install theme', 'dwp-cf' ),
				href:    admin_url( 'options-general.php?page=git-updater&tab=git_updater_install_theme' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Git Updater: Install Plugin', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-git-updater-remote-management',
				title:   __( 'Remote Management', 'dwp-cf' ),
				href:    admin_url( 'options-general.php?page=git-updater&tab=git_updater_remote_management' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Git Updater: Remote Management', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
		);
	}

	#[Override]
	protected function get_menu_nodes(): array { return array(); }
}
