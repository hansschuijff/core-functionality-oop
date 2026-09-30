<?php
namespace DeWittePrins\CoreFunctionality\Environment\Plugins;

use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\DTO\ToolbarShortcut;
use DeWittePrins\CoreFunctionality\Environment\DTO\ProofRequirement;
use Override;

use function __;
use function admin_url;

class LocoTranslate extends AbstractPlugin {

	#[Override]
	public function get_id(): string {
		return 'loco-translate';
	}

	#[Override]
	protected function get_proof_requirements(): ProofRequirement {
		return new ProofRequirement( classes: array( 'Loco_Empty_Project' ) );
	}

	#[Override]
	protected function get_toolbar_nodes(): array {
		return array(
			new ToolbarShortcut(
				id:      'cf-loco-translate-all',
				title:   __( 'Translations', 'dwp-cf' ),
				href:    admin_url( 'admin.php?page=loco' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Translations', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-loco-translate-plugins',
				title:   __( 'Plugin Translations', 'dwp-cf' ),
				href:    admin_url( 'admin.php?page=loco-plugin' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Plugin Translations', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:      'cf-loco-translate-themes',
				title:   __( 'Theme Translations', 'dwp-cf' ),
				href:    admin_url( 'admin.php?page=loco-theme' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Theme Translations', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
		);
	}

	#[Override]
	protected function get_menu_nodes(): array { return array(); }
}
