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
		return new ProofRequirement( constants: 'WP_REDIS_VERSION' );
	}

	#[Override]
	protected function get_toolbar_nodes(): array {
		return array(
			new ToolbarShortcut(
				id:      'cf-redis-cache',
				title:   __( 'Redis Cache', 'core-functionality' ),
				href:    admin_url( 'options-general.php?page=redis-cache' ),
				label:   __( 'Link to', 'core-functionality' ) . ' ' . __( 'Redis Cache Settings', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
		);
	}

	#[Override]
	protected function get_menu_nodes(): array { return array(); }
}
