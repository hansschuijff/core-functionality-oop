<?php
namespace DeWittePrins\CoreFunctionality\Environment\Plugins;

use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\DTO\ToolbarShortcut;
use DeWittePrins\CoreFunctionality\Environment\DTO\ProofRequirement;
use Override;

use function __;
use function admin_url;

class WpPhpMyAdmin extends AbstractPlugin {

	#[Override]
	public function get_id(): string {
		return 'wp-php-my-admin';
	}

	#[Override]
	protected function get_proof_requirements(): ProofRequirement {
		return new ProofRequirement( classes: array( 'WP_phpMyAdmin_Extension' ) );
	}

	#[Override]
	protected function get_toolbar_nodes(): array {
		return array(
			new ToolbarShortcut(
				id:      'cf-phpmyadmin',
				title:   __( 'Database editor', 'dwp-cf' ),
				href:    admin_url( 'options-general.php?page=wp-phpmyadmin-extension&isactivation' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'phpMyAdmin', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
		);
	}

	#[Override]
	protected function get_menu_nodes(): array { return array(); }
}
