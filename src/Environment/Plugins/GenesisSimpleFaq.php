<?php
/**
 * Genesis Simple FAQ Verification Guard.
 *
 * @package DeWittePrins\CoreFunctionality\Environment\Plugins
 * @author  Hans Schuijff <@hansschuijff>
 * @license GPL-2.0
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace DeWittePrins\CoreFunctionality\Environment\Plugins;

use DeWittePrins\CoreFunctionality\Environment\DTO\ProofRequirement;
use Override;

/**
 * Class GenesisSimpleFaq
 *
 * Reports whether Genesis Simple FAQ is active and loaded. Supplies no admin links yet.
 *
 * @since 1.0.0
 */
class GenesisSimpleFaq extends AbstractPlugin {

	#[Override]
	public function get_id(): string {
		return 'genesis-simple-faq';
	}

	#[Override]
	protected function get_proof_requirements(): ProofRequirement {
		return new ProofRequirement( constants: 'GENESIS_SIMPLE_PLUGIN_VERSION' );
	}

	#[Override]
	protected function get_toolbar_nodes(): array {
		return array();
	}

	#[Override]
	protected function get_menu_nodes(): array {
		return array();
	}
}
