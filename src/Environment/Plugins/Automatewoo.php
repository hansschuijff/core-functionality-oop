<?php
/**
 * AutomateWoo Verification Guard.
 *
 * @package DeWittePrins\CoreFunctionality\Environment\Plugins
 * @author  Hans Schuijff <@hansschuijff>
 * @license GPL-2.0
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace DeWittePrins\CoreFunctionality\Environment\Plugins;

use DeWittePrins\CoreFunctionality\Enums\Orientation;
use DeWittePrins\CoreFunctionality\DTO\ToolbarShortcut;
use DeWittePrins\CoreFunctionality\Environment\DTO\ProofRequirement;
use Override;

use function __;
use function admin_url;

/**
 * Class Automatewoo
 *
 * Reports whether AutomateWoo is active and started. AutomateWoo replaces WooCommerce
 * Follow-Up Emails on Hans's sites.
 *
 * @since 1.0.0
 */
class Automatewoo extends AbstractPlugin {

	#[Override]
	public function get_id(): string {
		return 'automatewoo';
	}

	#[Override]
	protected function get_proof_requirements(): ProofRequirement {
		// AutomateWoo only loads these (plugins_loaded 8) when its PHP, WordPress and WooCommerce
		// requirements are met, so they prove the plugin really started.
		return new ProofRequirement(
			classes:   'AutomateWoo',
			functions: 'AW'
		);
	}

	#[Override]
	protected function get_toolbar_nodes(): array {
		return array(
			new ToolbarShortcut(
				id:          'cf-automatewoo-dashboard',
				title:       __( 'AutomateWoo', 'core-functionality' ),
				href:        admin_url( 'admin.php?page=automatewoo-dashboard' ),
				label:       __( 'Link to', 'core-functionality' ) . ' ' . __( 'AutomateWoo Dashboard', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
			new ToolbarShortcut(
				id:          'cf-automatewoo-workflows',
				title:       __( 'Workflows', 'core-functionality' ),
				href:        admin_url( 'edit.php?post_type=aw_workflow' ),
				label:       __( 'Link to', 'core-functionality' ) . ' ' . __( 'AutomateWoo Workflows', 'core-functionality' ),
				orientation: Orientation::ADMIN,
			),
		);
	}

	#[Override]
	protected function get_menu_nodes(): array {
		return array();
	}
}
