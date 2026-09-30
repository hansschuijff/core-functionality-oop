<?php
namespace DeWittePrins\CoreFunctionality\Environment\Plugins;

use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\DTO\ToolbarShortcut;
use DeWittePrins\CoreFunctionality\Environment\DTO\ProofRequirement;
use Override;

use function __;
use function admin_url;

class RedisCache extends AbstractPlugin {

	#[Override]
	public function get_id(): string {
		return 'redis-cache';
	}

	#[Override]
	protected function get_proof_requirements(): ProofRequirement {
		return new ProofRequirement( classes: array( 'RedisCache' ) );
	}

	#[Override]
	protected function get_toolbar_nodes(): array {
		return array(
			new ToolbarShortcut(
				id:      'cf-redis-cache',
				title:   __( 'Redis Cache', 'dwp-cf' ),
				href:    admin_url( 'options-general.php?page=redis-cache' ),
				label:   __( 'Link to', 'dwp-cf' ) . ' ' . __( 'Redis Cache Settings', 'dwp-cf' ),
				orientation: Orientation::ADMIN,
			),
		);
	}

	#[Override]
	protected function get_menu_nodes(): array { return array(); }
}
