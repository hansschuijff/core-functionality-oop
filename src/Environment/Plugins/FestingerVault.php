<?php
namespace DeWittePrins\CoreFunctionality\Environment\Plugins;

use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\DTO\ToolbarShortcut;
use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\Environment\DTO\ProofRequirement;
use Override;

use function admin_url;
use function __;

class FestingerVault extends AbstractPlugin {

	#[Override]
	public function get_id(): string {
		return 'festinger-vault';
	}

	#[Override]
	protected function get_proof_requirements(): ProofRequirement {
		return new ProofRequirement( actions: array( 'festinger_vault_loaded' ) );
	}

	#[Override]
	protected function get_toolbar_nodes(): array {
		return array(
			new ToolbarShortcut(
				id:          'cf-festinger-vault-menu',
				title:       __( 'Festinger Vault', 'core-functionality' ),
				href:        admin_url( 'admin.php?page=festingervault#/updates' ),
				label:       __( 'Link to', 'core-functionality' ) . ' ' . __( 'Festinger Vault', 'core-functionality' ) . '' . __( 'Plugin Updates', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-festinger-vault-plugin-updates',
				title:       __( 'Updates', 'core-functionality' ),
				href:        admin_url( 'admin.php?page=festingervault#/updates' ),
				label:       __( 'Link to', 'core-functionality' ) . ' ' . __( 'Festinger Vault', 'core-functionality' ) . '' . __( 'Plugin Updates', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-festinger-vault-plugins',
				title:       __( 'Plugins', 'core-functionality' ),
				href:        admin_url( 'admin.php?page=festingervault#/item/wordpress-plugins' ),
				label:       __( 'Link to', 'core-functionality' ) . ' ' . __( 'Festinger Vault', 'core-functionality' ) . '' . __( 'Plugins', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-festinger-vault-themes',
				title:       __( 'Themes', 'core-functionality' ),
				href:        admin_url( 'admin.php?page=festingervault#/item/wordpress-themes' ),
				label:       __( 'Link to', 'core-functionality' ) . ' ' . __( 'Festinger Vault', 'core-functionality' ) . '' . __( 'Themes', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-festinger-vault-elementor-template-kits',
				title:       __( 'Elementor Template Kits', 'core-functionality' ),
				href:        admin_url( 'admin.php?page=festingervault#/item/elementor-template-kits' ),
				label:       __( 'Link to', 'core-functionality' ) . ' ' . __( 'Festinger Vault', 'core-functionality' ) . '' . __( 'Elementor Template Kits', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-festinger-vault-history',
				title:       __( 'History', 'core-functionality' ),
				href:        admin_url( 'admin.php?page=festingervault#/history' ),
				label:       __( 'Link to', 'core-functionality' ) . ' ' . __( 'Festinger Vault', 'core-functionality' ) . '' . __( 'History', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-festinger-vault-settings',
				title:       __( 'Settings', 'core-functionality' ),
				href:        admin_url( 'admin.php?page=festingervault#/settings' ),
				label:       __( 'Link to', 'core-functionality' ) . ' ' . __( 'Festinger Vault', 'core-functionality' ) . '' . __( 'Settings', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-festinger-vault-support',
				title:       __( 'Support', 'core-functionality' ),
				href:        admin_url( 'admin.php?page=festingervault#/need-help' ),
				label:       __( 'Link to', 'core-functionality' ) . ' ' . __( 'Festinger Vault', 'core-functionality' ) . '' . __( 'support', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
		);
	}

	#[Override]
	protected function get_menu_nodes(): array { return array(); }
}
