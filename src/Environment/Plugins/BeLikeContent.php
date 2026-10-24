<?php
/**
 * BE Like Content Verification Guard.
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
 * Class BeLikeContent
 *
 * Reports whether BE Like Content is active and loaded. Supplies no admin links yet.
 *
 * @since 1.0.0
 */
class BeLikeContent extends AbstractPlugin {

	#[Override]
	public function get_id(): string {
		return 'be-like-content';
	}

	#[Override]
	protected function get_proof_requirements(): ProofRequirement {
		return new ProofRequirement( classes: 'BE_Like_Content' );
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
