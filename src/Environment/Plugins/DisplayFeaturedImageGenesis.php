<?php
/**
 * Display Featured Image for Genesis Verification Guard.
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
 * Class DisplayFeaturedImageGenesis
 *
 * Reports whether Display Featured Image for Genesis is active and loaded. Supplies no admin links yet.
 *
 * @since 1.0.0
 */
class DisplayFeaturedImageGenesis extends AbstractPlugin {

	#[Override]
	public function get_id(): string {
		return 'display-featured-image-genesis';
	}

	#[Override]
	protected function get_proof_requirements(): ProofRequirement {
		return new ProofRequirement( functions: 'display_featured_image_genesis_require' );
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
